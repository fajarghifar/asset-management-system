<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Enums\LocationSite;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $targetSites = [
            LocationSite::JMP2,
            LocationSite::TGS,
            LocationSite::BT,
        ];

        foreach ($targetSites as $site) {
            $name = 'Ruang IT';
            $code = "{$site->value}-{$name}";

            Location::firstOrCreate(
                [
                    'site' => $site->value,
                    'name' => $name,
                ],
                [
                    'code' => $code,
                    'description' => "Pusat server dan operasional IT Staff di area {$site->getLabel()}.",
                ]
            );

            $this->command->info("✅ Lokasi '{$name}' ({$code}) berhasil dibuat di {$site->value}");
        }
    }
}
