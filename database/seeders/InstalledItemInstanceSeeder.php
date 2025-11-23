<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstalledItemInstance;
use App\Models\Location;
use App\Models\Item;

class InstalledItemInstanceSeeder extends Seeder
{
    public function run(): void
    {
        // === 1. Ambil lokasi yang dibutuhkan ===
        $jmp1Meeting = Location::where('code', 'JMP1-RM1')->first(); // Ruang Meeting JMP 1
        $jmp1Server = Location::where('code', 'JMP1-SRV')->first(); // Ruang Server JMP 1
        $btEvent = Location::where('code', 'BT-EVT')->first();   // Ruang Event BT
        $btShop = Location::where('code', 'BT-SHOP1')->first(); // Area Display BT

        $requiredLocations = [
            'JMP1-RM1' => $jmp1Meeting,
            'JMP1-SRV' => $jmp1Server,
            'BT-EVT' => $btEvent,
            'BT-SHOP1' => $btShop,
        ];

        foreach ($requiredLocations as $code => $location) {
            if (!$location) {
                $this->command->error("Lokasi dengan kode '{$code}' tidak ditemukan!");
                return;
            }
        }

        // === 2. Ambil item yang dibutuhkan ===
        $laptopId = Item::where('code', 'LAPTOP-DELL-3400')->value('id');
        $projectorId = Item::where('code', 'PROJ-EPSON-EBU')->value('id');
        $printerId = Item::where('code', 'PRINTER-HP-MFP')->value('id');
        $wifiApId = Item::where('code', 'WIFI-AP-RUIJIE')->value('id');
        $switchId = Item::where('code', 'SWITCH-TP-LINK')->value('id');
        $cctvId = Item::where('code', 'CCTV-HIKVISION')->value('id');

        $requiredItems = [
            'LAPTOP-DELL-3400' => $laptopId,
            'PROJ-EPSON-EBU' => $projectorId,
            'PRINTER-HP-MFP' => $printerId,
            'WIFI-AP-RUIJIE' => $wifiApId,
            'SWITCH-TP-LINK' => $switchId,
            'CCTV-HIKVISION' => $cctvId,
        ];

        foreach ($requiredItems as $code => $id) {
            if (!$id) {
                $this->command->error("Item dengan kode '{$code}' tidak ditemukan!");
                return;
            }
        }

        // === 3. Buat data InstalledItemInstance ===
        InstalledItemInstance::create([
            'code' => 'LAP-BT-001',
            'item_id' => $laptopId,
            'serial_number' => 'DL2023L003',
            'current_location_id' => $btShop->id,
            'installed_at' => now()->subMonths(3),
            'notes' => 'Untuk demo produk batik digital',
        ]);

        InstalledItemInstance::create([
            'code' => 'LAP-JMP1-001',
            'item_id' => $laptopId,
            'serial_number' => 'DL2023L001',
            'current_location_id' => $jmp1Meeting->id,
            'installed_at' => now()->subMonths(3),
            'notes' => 'Laptop cadangan untuk presentasi',
        ]);

        InstalledItemInstance::create([
            'code' => 'LAP-JMP1-002',
            'item_id' => $laptopId,
            'serial_number' => 'DL2023L002',
            'current_location_id' => $btEvent->id,
            'installed_at' => now()->subMonths(3),
            'notes' => 'Dipinjam tim instalasi untuk proyek BT',
        ]);

        InstalledItemInstance::create([
            'code' => 'PROJ-JMP1-001',
            'item_id' => $projectorId,
            'serial_number' => 'EP2023P001',
            'current_location_id' => $jmp1Meeting->id,
            'installed_at' => now()->subMonths(3),
            'notes' => 'Proyektor utama ruang meeting',
        ]);

        // ✅ Perbaikan utama: tambahkan lokasi dan installed_at untuk printer
        InstalledItemInstance::create([
            'code' => 'PRN-JMP1-001',
            'item_id' => $printerId,
            'serial_number' => 'HP2023M001',
            'current_location_id' => $jmp1Server->id, // Misalnya ditempatkan di ruang server
            'installed_at' => now()->subWeeks(2),
            'notes' => 'Sedang diperbaiki karena kerusakan kertas',
        ]);

        InstalledItemInstance::create([
            'code' => 'AP-JMP1-001',
            'item_id' => $wifiApId,
            'serial_number' => 'RJ2023A001',
            'current_location_id' => $jmp1Meeting->id,
            'installed_at' => now()->subMonths(5),
            'notes' => 'Dipasang untuk support meeting hybrid',
        ]);

        InstalledItemInstance::create([
            'code' => 'AP-BT-001',
            'item_id' => $wifiApId,
            'serial_number' => 'RJ2023B001',
            'current_location_id' => $btEvent->id,
            'installed_at' => now()->subMonths(3),
            'notes' => 'Dipasang untuk workshop batik',
        ]);

        InstalledItemInstance::create([
            'code' => 'SW-JMP1-001',
            'item_id' => $switchId,
            'serial_number' => 'TL2023S001',
            'current_location_id' => $jmp1Server->id,
            'installed_at' => now()->subMonths(8),
            'notes' => 'Switch utama jaringan kantor',
        ]);

        InstalledItemInstance::create([
            'code' => 'CCTV-BT-001',
            'item_id' => $cctvId,
            'serial_number' => 'HV2023C001',
            'current_location_id' => $btShop->id,
            'installed_at' => now()->subMonths(6),
            'notes' => 'Pengawasan area penjualan',
        ]);

        $this->command->info('✅ InstalledItemInstanceSeeder: 9 instance berhasil di-seed.');
    }
}
