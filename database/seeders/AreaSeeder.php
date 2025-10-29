<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            // Perumahan (housing)
            // [
            //     'code' => 'PH-A',
            //     'name' => 'Perumahan A',
            //     'category' => 'housing',
            //     'address' => 'Jl. Perumahan Indah No. 1, Jakarta',
            // ],
            // [
            //     'code' => 'PH-B',
            //     'name' => 'Perumahan B',
            //     'category' => 'housing',
            //     'address' => 'Jl. Griya Asri No. 10, Bandung',
            // ],

            [
                'code' => 'OFF-JMP1',
                'name' => 'JMP 1',
                'category' => 'office',
                'address' => 'Jl. H. Abas No.48, Trusmi Kulon, Kec. Weru, Kabupaten Cirebon, Jawa Barat 45154',
            ],
            [
                'code' => 'OFF-JMP2',
                'name' => 'JMP 2',
                'category' => 'office',
                'address' => 'Jl. H. Abas No.48, Trusmi Kulon, Kec. Weru, Kabupaten Cirebon, Jawa Barat 45154',
            ],

            // Store (store)
            [
                'code' => 'STORE-BT',
                'name' => 'BT Batik Trusmi',
                'category' => 'store',
                'address' => 'Jl. Trusmi No.148, Weru Lor, Kec. Plered, Kabupaten Cirebon, Jawa Barat 45154',
            ],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}
