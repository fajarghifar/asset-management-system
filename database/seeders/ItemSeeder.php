<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        // Consumable
        Item::create([
            'code' => 'RJ45-8P8C',
            'name' => 'Konektor RJ45',
            'type' => 'consumable',
            'description' => 'Konektor jaringan 8P8C',
        ]);

        Item::create([
            'code' => 'CABLE-LAN-CAT6',
            'name' => 'Kabel LAN Cat6 2m',
            'type' => 'consumable',
            'description' => 'Kabel jaringan UTP Cat6',
        ]);

        // Fixed
        Item::create([
            'code' => 'TANG-CRIMP',
            'name' => 'Tang Crimping Belden',
            'type' => 'fixed',
            'description' => 'Alat crimping konektor RJ45',
        ]);
        Item::create([
            'code' => 'TOOLBOX-BASIC',
            'name' => 'Toolbox Basic',
            'type' => 'fixed',
            'description' => 'Set peralatan dasar untuk instalasi jaringan',
        ]);
        Item::create([
            'code' => 'LAPTOP-DELL-3400',
            'name' => 'Laptop Dell Latitude 3400',
            'type' => 'fixed',
            'description' => 'Laptop untuk staff',
        ]);

        Item::create([
            'code' => 'PROJ-EPSON-EBU',
            'name' => 'Proyektor Epson EBU',
            'type' => 'fixed',
            'description' => 'Proyektor untuk presentasi',
        ]);

        // Installed
        Item::create([
            'code' => 'WIFI-AP-RUIJIE',
            'name' => 'Wi-Fi AP Ruijie RG-AP860',
            'type' => 'installed',
            'description' => 'Access point nirkabel',
        ]);

        Item::create([
            'code' => 'CCTV-HIKVISION',
            'name' => 'Kamera CCTV Hikvision',
            'type' => 'installed',
            'description' => 'Kamera pengawas toko',
        ]);
    }
}
