<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Komputer & Laptop',
                'description' => 'Perangkat komputasi utama seperti PC Desktop, Laptop, dan Workstation.',
            ],
            [
                'name' => 'Perangkat Jaringan',
                'description' => 'Infrastruktur jaringan termasuk Router, Switch, Modem, dan Access Point.',
            ],
            [
                'name' => 'Keamanan & CCTV',
                'description' => 'Sistem keamanan, kamera pengawas, dan perangkat kontrol akses.',
            ],
            [
                'name' => 'Suku Cadang',
                'description' => 'Komponen pengganti dan sparepart untuk perbaikan perangkat keras.',
            ],
            [
                'name' => 'Aksesoris Komputer',
                'description' => 'Perangkat tambahan seperti Mouse, Keyboard, Headset, dan adaptor.',
            ],
            [
                'name' => 'Peralatan Kerja',
                'description' => 'Alat-alat teknis untuk pemeliharaan dan perbaikan seperti Obeng, Tang, dan Multitester.',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );
        }

        $this->command->info('Categories seeded successfully.');
    }
}
