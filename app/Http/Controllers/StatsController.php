<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $site = $this->getSite($request);

        return view('stats', [
            'site' => $site,
        ]);
    }

    public function hourly(Request $request)
    {
        $site = $this->getSite($request);

        // Last 24 hours by default
        $to = now();
        $from = $to->copy()->subHours(24);

        $rows = Visit::query()
            ->where('site_id', $site->id)
            ->whereBetween('visited_at', [$from, $to])
            ->select([
                DB::raw("strftime('%Y-%m-%d %H:00:00', visited_at) as hour_bucket"),
                DB::raw('count(distinct ip) as unique_visits'),
            ])
            ->groupBy('hour_bucket')
            ->orderBy('hour_bucket')
            ->get();

        return response()->json([
            'from' => $from->toISOString(),
            'to' => $to->toISOString(),
            'labels' => $rows->pluck('hour_bucket'),
            'values' => $rows->pluck('unique_visits'),
        ]);
    }

    public function cities(Request $request)
    {
        $site = $this->getSite($request);

        $to = now();
        $from = $to->copy()->subHours(24);

        $rows = Visit::query()
            ->where('site_id', $site->id)
            ->whereBetween('visited_at', [$from, $to])
            ->select([
                DB::raw("coalesce(nullif(city, ''), 'Unknown') as city_name"),
                DB::raw('count(distinct ip) as unique_visits'),
            ])
            ->groupBy('city_name')
            ->orderByDesc('unique_visits')
            ->limit(12)
            ->get();

        return response()->json([
            'from' => $from->toISOString(),
            'to' => $to->toISOString(),
            'labels' => $rows->pluck('city_name'),
            'values' => $rows->pluck('unique_visits'),
        ]);
    }

    private function getSite(Request $request): Site
    {
        // For this test task we use a single default site; later you can expand to multi-site selection.
        return Site::query()->firstOrFail();
    }
}
