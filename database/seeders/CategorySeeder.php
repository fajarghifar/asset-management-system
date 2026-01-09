<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Tools
            [
                'name' => 'Perkakas & Peralatan',
                'slug' => 'perkakas-peralatan',
                'description' => 'Perkakas kerja teknisi, alat mekanik, dan peralatan listrik (Tang, Obeng, Blower, Solder).',
            ],

            // Testing
            [
                'name' => 'Alat Ukur & Pengujian',
                'slug' => 'alat-ukur-pengujian',
                'description' => 'Alat pengujian dan pengukuran parameter jaringan/listrik (LAN Tester, Multimeter, OPM).',
            ],

            // Network
            [
                'name' => 'Infrastruktur Jaringan',
                'slug' => 'infrastruktur-jaringan',
                'description' => 'Perangkat keras jaringan, komunikasi, pengkabelan, dan konektor (Switch, HT, RJ45, Kabel LAN).',
            ],

            // Components
            [
                'name' => 'Komponen Komputer',
                'slug' => 'komponen-komputer',
                'description' => 'Komponen internal PC/Laptop dan media penyimpanan (Motherboard, HDD, SSD, RAM).',
            ],

            // Peripherals
            [
                'name' => 'Periferal & Aksesoris',
                'slug' => 'periferal-aksesoris',
                'description' => 'Perangkat tambahan komputer dan adapter (Keyboard, Mouse, Converter HDMI/VGA).',
            ],

            // Power
            [
                'name' => 'Sistem Daya & Listrik',
                'slug' => 'sistem-daya-listrik',
                'description' => 'Perangkat catu daya dan backup listrik (UPS, Stabilizer, Power Supply).',
            ],

            // Maintenance
            [
                'name' => 'Perlengkapan Perawatan',
                'slug' => 'perlengkapan-perawatan',
                'description' => 'Bahan pendukung perawatan dan perbaikan (Thermal Paste, Cleaning Kit, Timah Solder, Isolasi).',
            ],
        ];

        DB::transaction(function () use ($categories) {
            foreach ($categories as $cat) {
                // Upsert logic or updateOrCreate
                Category::updateOrCreate(
                    ['slug' => $cat['slug']],
                    [
                        'name' => $cat['name'],
                        'description' => $cat['description'],
                    ]
                );
            }
        });
    }
}
