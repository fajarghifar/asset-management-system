<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Enums\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->pluck('id', 'name');

        if ($categories->isEmpty()) {
            throw new \Exception("❌ ERROR: Categories empty. Run CategorySeeder first!");
        }

        $getCatId = fn($name) => $categories[$name]
            ?? throw new \Exception("❌ ERROR: Category '$name' not found in database.");

        // Define Category Map
        $catMap = [
            'TOOLS' => $getCatId('Peralatan Kerja'),
            'TEST' => $getCatId('Peralatan Kerja'),
            'NET' => $getCatId('Perangkat Jaringan'),
            'CCTV' => $getCatId('Keamanan & CCTV'),
            'COMP' => $getCatId('Komputer & Laptop'),
            'PERI' => $getCatId('Aksesoris Komputer'),
            'POWR' => $getCatId('Aksesoris Komputer'),
            'MAINT' => $getCatId('Suku Cadang'),
        ];

        DB::transaction(function () use ($catMap) {
            foreach ($this->getProductDefinitions() as $groupKey => $def) {
                $categoryId = $catMap[$def['category_key']] ?? null;

                if (!$categoryId) {
                    $this->command->warn("⚠️ Skipping group '$groupKey': Category ID not mapped.");
                    continue;
                }

                $this->seedBatch(
                    $def['items'],
                    $def['type'],
                    $categoryId,
                    $def['loanable']
                );
            }
        });
    }

    private function seedBatch(array $items, ProductType $type, int $categoryId, bool $isLoanable = true): void
    {
        $now = now();
        $data = [];

        foreach ($items as $code => $name) {
            $data[] = [
                'code' => strtoupper(trim($code)),
                'name' => $name,
                'type' => $type->value,
                'category_id' => $categoryId,
                'can_be_loaned' => $isLoanable,
                'description' => "Initial Import ({$type->getLabel()})",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Product::upsert(
            $data,
            ['code'],
            ['name', 'type', 'category_id', 'can_be_loaned', 'description', 'updated_at']
        );

        $this->command->info("✅ Seeded " . count($data) . " items | Type: {$type->name}");
    }

    private function getProductDefinitions(): array
    {
        return [
            // Assets
            'TOOLS' => [
                'type' => ProductType::Asset,
                'category_key' => 'TOOLS',
                'loanable' => true,
                'items' => [
                    'TANGPT' => 'Tang Potong',
                    'TANGLC' => 'Tang Lancip',
                    'TANGBS' => 'Tang Biasa',
                    'TPKCL' => 'Tang Potong Kecil',
                    'TLKCL' => 'Tang Lancip Kecil',
                    'TBKCL' => 'Tang Biasa Kecil',
                    'CRIMP' => 'Tang Crimping',
                    'GNTBS' => 'Gunting Besar',
                    'GNTKC' => 'Gunting Kecil',
                    'CUTTER' => 'Pisau Cutter',
                    'GERGAJ' => 'Gergaji Kecil',
                    'OBLPT' => 'Obeng Set Laptop',
                    'OB115' => 'Obeng Set 115 in 1',
                    'OBKNG' => 'Obeng Kuning',
                    'OBSTD' => 'Obeng Standar',
                    'TOOLKT' => 'Toolkit Satu Set',
                    'TKPALU' => 'Toolkit Set Lengkap',
                    'GLUEGN' => 'Alat Lem Tembak (Glue Gun)',
                    'BLOWER' => 'Blower / Heat Gun',
                    'SUNTIK' => 'Suntikan Besar (Refill)',
                ]
            ],
            'TESTING' => [
                'type' => ProductType::Asset,
                'category_key' => 'TEST',
                'loanable' => true,
                'items' => [
                    'LANTST' => 'LAN Tester',
                    'MULTI' => 'Multi Meter Digital',
                    'OPM' => 'Optical Power Meter (OPM)',
                    'PSTEST' => 'Power Supply Tester',
                    'WTRPAS' => 'Waterpass',
                ]
            ],
            'NETWORK_HW' => [
                'type' => ProductType::Asset,
                'category_key' => 'NET',
                'loanable' => true,
                'items' => [
                    'HT' => 'Handy Talky (HT)',
                    'IPPHON' => 'Fanvil (IP Phone)',
                    'POE' => 'POE Injector',
                ]
            ],
            'POWER' => [
                'type' => ProductType::Asset,
                'category_key' => 'POWR',
                'loanable' => false,
                'items' => [
                    'UPS' => 'UPS 600VA',
                ]
            ],
            'COMPONENTS' => [
                'type' => ProductType::Asset,
                'category_key' => 'COMP',
                'loanable' => false,
                'items' => [
                    'WD500' => 'Harddisk WD 500GB',
                    'SGT500' => 'Harddisk Seagate 500GB',
                    'WD320' => 'Harddisk WD 320GB',
                    'SGT1TB' => 'Harddisk Seagate 1TB',
                    'SGT250' => 'Harddisk Seagate 250GB',
                    'HDDLAP' => 'Harddisk Laptop (General)',
                    'MOBO' => 'Motherboard PC',
                ]
            ],
            'PERIPHERALS' => [
                'type' => ProductType::Asset,
                'category_key' => 'PERI',
                'loanable' => true,
                'items' => [
                    'STB' => 'STB (Set Top Box)',
                    'HDDEXT' => 'Harddisk External',
                    'KEYMIN' => 'Keyboard Mini',
                    'HEADLP' => 'Lampu Kepala',
                    'CNHDMI' => 'Converter HDMI to USB',
                    'CNVGA' => 'Converter VGA to USB',
                ]
            ],

            // Consumables
            'NET_CONSUMABLES' => [
                'type' => ProductType::Consumable,
                'category_key' => 'NET',
                'loanable' => true,
                'items' => [
                    'RJ45' => 'Konektor RJ45',
                    'RJ11' => 'Konektor RJ11',
                    'FEMALE' => 'Konektor Female',
                    'RG4' => 'Kabel RG4 / Coaxial',
                ]
            ],
            'MAINTENANCE' => [
                'type' => ProductType::Consumable,
                'category_key' => 'MAINT',
                'loanable' => true,
                'items' => [
                    'CLKIT' => 'Cleaning Kit',
                    'PASTA' => 'Pasta Processor (Thermal Paste)',
                    'CMOS' => 'Baterai CMOS 2032',
                    'JACKDC' => 'Jack DC Male',
                ]
            ],
        ];
    }
}
