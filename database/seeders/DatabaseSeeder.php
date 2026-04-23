<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            NavItemSeeder::class,
            TickerItemSeeder::class,
            AboutLedgerRowSeeder::class,
            MetricTileSeeder::class,
            ServiceSeeder::class,
            BitrixSeeder::class,
            IndustrySeeder::class,
            WasteTypeSeeder::class,
            ProcessStepSeeder::class,
            PlanSeeder::class,
            CoverageSeeder::class,
            FooterSeeder::class,
            SiteAssetSeeder::class,
        ]);

        // Bust the settings cache so the first request after seeding reads fresh values.
        Cache::flush();
    }
}
