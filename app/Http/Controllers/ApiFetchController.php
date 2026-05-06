<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiFetchResource;
use App\Models\ApiFetch;
use Illuminate\Http\Request;

class ApiFetchController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min(200, $limit));

        $items = ApiFetch::query()
            ->orderByDesc('fetched_at')
            ->limit($limit)
            ->get();

        return ApiFetchResource::collection($items);
    }
}
