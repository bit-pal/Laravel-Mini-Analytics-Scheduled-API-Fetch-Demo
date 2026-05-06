<?php

namespace App\Support;

final class DeviceDetector
{
    public static function fromUserAgent(?string $userAgent): string
    {
        $ua = strtolower((string) $userAgent);

        if ($ua === '') {
            return 'unknown';
        }

        // Coarse heuristic: good enough for a test assignment.
        if (str_contains($ua, 'ipad') || (str_contains($ua, 'android') && ! str_contains($ua, 'mobile'))) {
            return 'tablet';
        }

        if (
            str_contains($ua, 'mobi')
            || str_contains($ua, 'iphone')
            || str_contains($ua, 'ipod')
            || str_contains($ua, 'android')
        ) {
            return 'mobile';
        }

        return 'desktop';
    }
}

