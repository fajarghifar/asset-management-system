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
    private int $sequence = 0;
    private string $year;

    public function __construct()
    {
        $this->year = date('Y');
    }

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

        // Initialize Sequence
        $prefix = "PRD.{$this->year}.";
        $latest = Product::where('code', 'like', "{$prefix}%")
            ->orderByRaw('LENGTH(code) DESC')
            ->orderBy('code', 'desc')
            ->first();

        if ($latest) {
            $this->sequence = (int) str_replace($prefix, '', $latest->code);
        }

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
        $prefix = "PRD.{$this->year}.";

        foreach ($items as $name) {
            // Check if product with this name already exists to avoid duplicates
            $existing = Product::where('name', $name)->first();

            if ($existing) {
                // Determine if we need to update it (e.g. category changed)
                // For now, we just skip or update category/loanable status
                $existing->update([
                    'type' => $type->value,
                    'category_id' => $categoryId,
                    'can_be_loaned' => $isLoanable,
                    'updated_at' => $now,
                ]);
                continue;
            }

            $this->sequence++;
            $code = $prefix . str_pad($this->sequence, 3, '0', STR_PAD_LEFT);

            Product::create([
                'code' => $code,
                'name' => $name,
                'type' => $type->value,
                'category_id' => $categoryId,
                'can_be_loaned' => $isLoanable,
                'description' => "Initial Import ({$type->getLabel()})",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info("✅ Batch Processed | Type: {$type->name}");
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
                    'Tang Potong',
                    'Tang Lancip',
                    'Tang Biasa',
                    'Tang Potong Kecil',
                    'Tang Lancip Kecil',
                    'Tang Biasa Kecil',
                    'Tang Crimping',
                    'Gunting Besar',
                    'Gunting Kecil',
                    'Pisau Cutter',
                    'Gergaji Kecil',
                    'Obeng Set Laptop',
                    'Obeng Set 115 in 1',
                    'Obeng Kuning',
                    'Obeng Standar',
                    'Toolkit Satu Set',
                    'Toolkit Set Lengkap',
                    'Alat Lem Tembak (Glue Gun)',
                    'Blower / Heat Gun',
                    'Suntikan Besar (Refill)',
                ]
            ],
            'TESTING' => [
                'type' => ProductType::Asset,
                'category_key' => 'TEST',
                'loanable' => true,
                'items' => [
                    'LAN Tester',
                    'Multi Meter Digital',
                    'Optical Power Meter (OPM)',
                    'Power Supply Tester',
                    'Waterpass',
                ]
            ],
            'NETWORK_HW' => [
                'type' => ProductType::Asset,
                'category_key' => 'NET',
                'loanable' => true,
                'items' => [
                    'Handy Talky (HT)',
                    'Fanvil (IP Phone)',
                    'POE Injector',
                ]
            ],
            'POWER' => [
                'type' => ProductType::Asset,
                'category_key' => 'POWR',
                'loanable' => false,
                'items' => [
                    'UPS 600VA',
                ]
            ],
            'COMPONENTS' => [
                'type' => ProductType::Asset,
                'category_key' => 'COMP',
                'loanable' => false,
                'items' => [
                    'Harddisk WD 500GB',
                    'Harddisk Seagate 500GB',
                    'Harddisk WD 320GB',
                    'Harddisk Seagate 1TB',
                    'Harddisk Seagate 250GB',
                    'Harddisk Laptop (General)',
                    'Motherboard PC',
                ]
            ],
            'PERIPHERALS' => [
                'type' => ProductType::Asset,
                'category_key' => 'PERI',
                'loanable' => true,
                'items' => [
                    'STB (Set Top Box)',
                    'Harddisk External',
                    'Keyboard Mini',
                    'Lampu Kepala',
                    'Converter HDMI to USB',
                    'Converter VGA to USB',
                ]
            ],
            // Consumables
            'NET_CONSUMABLES' => [
                'type' => ProductType::Consumable,
                'category_key' => 'NET',
                'loanable' => true,
                'items' => [
                    'Konektor RJ45',
                    'Konektor RJ11',
                    'Konektor Female',
                    'Kabel RG4 / Coaxial',
                ]
            ],
            'MAINTENANCE' => [
                'type' => ProductType::Consumable,
                'category_key' => 'MAINT',
                'loanable' => true,
                'items' => [
                    'Cleaning Kit',
                    'Pasta Processor (Thermal Paste)',
                    'Baterai CMOS 2032',
                    'Jack DC Male',
                ]
            ],
        ];
    }
}
