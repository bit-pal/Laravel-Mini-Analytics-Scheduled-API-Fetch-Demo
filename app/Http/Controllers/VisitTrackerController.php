<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Visit;
use App\Support\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VisitTrackerController extends Controller
{
    public function trackerJs(Request $request)
    {
        $js = <<<'JS'
(function () {
  'use strict';

  function getTrackerScriptUrl() {
    var scripts = document.getElementsByTagName('script');
    for (var i = scripts.length - 1; i >= 0; i--) {
      var src = scripts[i].getAttribute('src') || '';
      if (src.indexOf('tracker.js') === -1) continue;
      try {
        return new URL(src, window.location.href);
      } catch (e) {}
    }
    // Fallback: if someone opens tracker.js directly in the browser, treat the current URL as the script URL.
    try {
      var current = new URL(window.location.href);
      if (current.pathname && current.pathname.toLowerCase().indexOf('tracker.js') !== -1) {
        return current;
      }
    } catch (e) {}
    return null;
  }

  function getSiteKeyFromScriptTag() {
    var u = getTrackerScriptUrl();
    if (!u) return null;
    var key = u.searchParams.get('siteKey');
    if (key) return key;
    return null;
  }

  function getEndpointUrl() {
    // IMPORTANT:
    // Post back to the same origin where tracker.js is hosted (keeps port, avoids APP_URL mismatch).
    var u = getTrackerScriptUrl();
    if (!u) return null;
    return new URL('/api/visit', u.origin).toString();
  }

  function payload(siteKey) {
    return {
      siteKey: siteKey,
      url: window.location.href,
      referrer: document.referrer || null,
      userAgent: navigator.userAgent || null,
      screen: (window.screen && window.screen.width && window.screen.height) ? (window.screen.width + 'x' + window.screen.height) : null,
      timezone: (Intl && Intl.DateTimeFormat) ? Intl.DateTimeFormat().resolvedOptions().timeZone : null,
      ts: Date.now()
    };
  }

  function send(endpoint, body) {
    var data = JSON.stringify(body);

    // Prefer sendBeacon for analytics.
    if (navigator.sendBeacon) {
      try {
        var blob = new Blob([data], { type: 'application/json' });
        navigator.sendBeacon(endpoint, blob);
        return;
      } catch (e) {}
    }

    try {
      fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: data,
        keepalive: true
      }).catch(function () {});
    } catch (e) {}
  }

  var siteKey = getSiteKeyFromScriptTag();
  if (!siteKey) return;
  var endpoint = getEndpointUrl();
  if (!endpoint) return;
  send(endpoint, payload(siteKey));
})();
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    public function collect(Request $request)
    {
        $data = $request->validate([
            'siteKey' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:2048'],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'userAgent' => ['nullable', 'string', 'max:2048'],
            'timezone' => ['nullable', 'string', 'max:128'],
            'ts' => ['nullable'],
        ]);

        $site = Site::query()->where('site_key', $data['siteKey'])->first();
        if (! $site) {
            return response()->json(['message' => 'Unknown siteKey'], 404);
        }

        $ip = $request->ip();
        $userAgent = $data['userAgent'] ?? $request->userAgent();
        $device = DeviceDetector::fromUserAgent($userAgent);

        $city = $this->resolveCity($ip);

        Visit::create([
            'site_id' => $site->id,
            'visited_at' => now(),
            'ip' => $ip,
            'city' => $city,
            'device' => $device,
            'user_agent' => $userAgent,
            'url' => $data['url'] ?? null,
            'referrer' => $data['referrer'] ?? null,
            'timezone' => $data['timezone'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    private function resolveCity(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        // Avoid calling geo API for local IPs / dev.
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Localhost';
        }

        return Cache::remember('geoip:city:'.$ip, now()->addDays(7), function () use ($ip) {
            try {
                // Free and simple; for production you’d use a paid provider or local DB.
                $resp = Http::timeout(5)->acceptJson()->get('http://ip-api.com/json/'.rawurlencode($ip), [
                    'fields' => 'status,city',
                ]);

                if (! $resp->ok()) {
                    return null;
                }

                $json = $resp->json();
                if (! is_array($json) || ($json['status'] ?? null) !== 'success') {
                    return null;
                }

                $city = $json['city'] ?? null;
                return is_string($city) && $city !== '' ? $city : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}
