<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Site::query()->firstOrCreate(
            ['name' => 'Default site'],
            [
                'site_key' => Str::random(32),
                'allowed_origins' => null,
            ]
        );
    }
}

