<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\FixedItemInstance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FixedItemInstanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jmp1Meeting = Location::where('code', 'JMP1-RM1')->first(); // Ruang Meeting JMP 1
        $jmp1Server = Location::where('code', 'JMP1-SRV')->first();  // Ruang Server JMP 1
        $jmp1Wh = Location::where('code', 'JMP1-WH1')->first();      // Gudang JMP 1
        $btEvent = Location::where('code', 'BT-EVT')->first();       // Ruang Event BT

        // ✅ Ganti validasi dengan array
        $requiredLocations = [
            'JMP1-RM1' => $jmp1Meeting,
            'JMP1-SRV' => $jmp1Server,
            'JMP1-WH1' => $jmp1Wh,
            'BT-EVT' => $btEvent,
        ];

        foreach ($requiredLocations as $code => $location) {
            if (!$location) {
                $this->command->error("Lokasi dengan kode '{$code}' tidak ditemukan!");
                return;
            }
        }

        // Tang Crimping
        FixedItemInstance::create([
            'code' => 'TANG-CRIMP-001',
            'item_id' => Item::where('code', 'TANG-CRIMP')->value('id'),
            'serial_number' => 'TC2023A001',
            'status' => 'available',
            'current_location_id' => $jmp1Wh->id,
            'notes' => 'Masih dalam garansi',
        ]);

        // Multimeter
        FixedItemInstance::create([
            'code' => 'MULTI-DT-001',
            'item_id' => Item::where('code', 'MULTIMETER-DT')->value('id'),
            'serial_number' => 'DT2023M001',
            'status' => 'available',
            'current_location_id' => $jmp1Wh->id,
            'notes' => 'Untuk pengukuran jaringan',
        ]);

        $this->command->info('✅ FixedItemInstanceSeeder: 7 instance berhasil di-seed.');
    }
}
