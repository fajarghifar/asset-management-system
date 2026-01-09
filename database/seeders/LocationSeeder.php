<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Enums\LocationSite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $targetSites = [
                LocationSite::JMP2,
                LocationSite::TGS,
                LocationSite::BT,
            ];

            $roomName = 'Ruang IT';
            $codeSuffix = 'RIT';

            foreach ($targetSites as $site) {
                $manualCode = "{$site->value}-{$codeSuffix}";

                $location = Location::firstOrCreate(
                    [
                        'site' => $site,
                        'name' => $roomName,
                    ],
                    [
                        'code' => $manualCode,
                        'description' => "Pusat server dan operasional IT Staff di area {$site->getLabel()}.",
                    ]
                );

                if ($location->wasRecentlyCreated) {
                    $this->command->info("✅ [NEW] Lokasi dibuat: {$roomName} ({$manualCode}) di {$site->getLabel()}");
                } else {
                    $this->command->warn("⚠️ [SKIP] Lokasi sudah ada: {$roomName} ({$manualCode}) di {$site->getLabel()}");
                }
            }
        });
    }
}
