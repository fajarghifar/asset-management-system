<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Product;
use App\Models\Location;
use App\Enums\AssetStatus;
use App\Enums\ProductType;
use App\Enums\LocationSite;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data Mapping
        $itemNameToProductName = $this->getItemMappings();
        $locationAliasToSite = [
            'TGS' => LocationSite::TGS,
            'BT Store' => LocationSite::BT,
            'JMP' => LocationSite::JMP2,
        ];

        // Fetch Dependencies by Name
        $products = Product::whereIn('name', array_unique($itemNameToProductName))
            ->where('type', ProductType::Asset)
            ->get()
            ->keyBy(fn($item) => strtoupper($item->name)); // Key by uppercase name for easier lookup

        if ($products->isEmpty()) {
            $this->command->error('No Asset products found. Run ProductSeeder first.');
            return;
        }

        $allLocations = Location::all();
        $locationLookup = $this->resolveLocations($allLocations, $locationAliasToSite);
        $rawData = $this->getRawData();

        $totalAssets = 0;
        $now = now();
        $dateCode = $now->format('ymd');

        $this->command->info("Starting Asset Seeding...");

        foreach ($rawData as $row) {
            $itemName = trim($row['item']);
            $targetProductName = $itemNameToProductName[$itemName] ?? null;

            if (!$targetProductName) {
                continue;
            }

            // Lookup product by uppercase name
            $product = $products->get(strtoupper($targetProductName));

            if (!$product) {
                continue;
            }

            $location = $locationLookup[$row['location']] ?? null;
            if (!$location) {
                continue; // Skip invalid locations
            }

            $qty = (int) $row['qty'];

            for ($i = 0; $i < $qty; $i++) {
                // Generate Unique Asset Tag
                do {
                    $randomCode = strtoupper(\Illuminate\Support\Str::random(4));
                    $assetTag = "INV.{$dateCode}.{$randomCode}";
                } while (Asset::where('asset_tag', $assetTag)->exists());

                Asset::create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'asset_tag' => $assetTag,
                    'status' => AssetStatus::InStock,
                    'purchase_date' => $now->copy()->subDays(rand(1, 730)),
                    'notes' => "Migrasi Asset (Asal: {$row['location']}, Item: $itemName)",
                ]);
            }

            $totalAssets += $qty;
        }

        $this->command->info("🎉 ASSET SEEDER: Total Assets Created: $totalAssets");
    }

    private function resolveLocations($allLocations, $aliases): array
    {
        $lookup = [];
        foreach ($aliases as $alias => $siteEnum) {
            $siteLocations = $allLocations->where('site', $siteEnum);

            // Prefer IT locations, fallback to any
            $preferred = $siteLocations->first(
                fn($loc) =>
                str_contains(strtolower($loc->name), 'it') ||
                str_contains(strtolower($loc->name), 'server')
            );

            $lookup[$alias] = $preferred ?? $siteLocations->first();
        }
        return $lookup;
    }

    private function getItemMappings(): array
    {
        return [
            'TANG POTONG' => 'Tang Potong',
            'TANG LANCIP' => 'Tang Lancip',
            'TANG BIASA' => 'Tang Biasa',
            'TANG POTONG KECIL' => 'Tang Potong Kecil',
            'TANG LANCIP KECIL' => 'Tang Lancip Kecil',
            'TANG BIASA KECIL' => 'Tang Biasa Kecil',
            'TANG CRIMPING' => 'Tang Crimping',
            'GUNTING BESAR' => 'Gunting Besar',
            'GUNTING' => 'Gunting Besar',
            'GUNTING KECIL' => 'Gunting Kecil',
            'PISAU CUTTER' => 'Pisau Cutter',
            'KATER' => 'Pisau Cutter',
            'GERGAJI KECIL' => 'Gergaji Kecil',
            'OBENG SET LAPTOP' => 'Obeng Set Laptop',
            'OBENG SET 115' => 'Obeng Set 115 in 1',
            'OBENG KUNING' => 'Obeng Kuning',
            'OBENG' => 'Obeng Standar',
            'OBENG STANDAR' => 'Obeng Standar',
            'TOOLKIT SATU SET' => 'Toolkit Satu Set',
            '1 SET TOOLKIT OBENG PALU Dll' => 'Toolkit Set Lengkap',
            'ALAT LEM TEMBAK' => 'Alat Lem Tembak (Glue Gun)',
            'BLOWER' => 'Blower / Heat Gun',
            'SUNTIKAN BESAR' => 'Suntikan Besar (Refill)',
            'LAN TESTER' => 'LAN Tester',
            'TESTER LAN' => 'LAN Tester',
            'MULTI METER DIGITAL' => 'Multi Meter Digital',
            'OPTICAL POWER METER (OPM)' => 'Optical Power Meter (OPM)',
            'POWER SUPPLY TESTER' => 'Power Supply Tester',
            'MATHERPAS' => 'Waterpass',
            'UPS' => 'UPS 600VA',
            'HT' => 'Handy Talky (HT)',
            'STB' => 'STB (Set Top Box)',
            'FANVIL' => 'Fanvil (IP Phone)',
            'POE' => 'POE Injector',
            'HARDISK EXTERNAL' => 'Harddisk External',
            'HARDIKS WD 500GB' => 'Harddisk WD 500GB',
            'HARDIKS SEAGATE 500GB' => 'Harddisk Seagate 500GB',
            'HARDIKS WD 320GB' => 'Harddisk WD 320GB',
            'HARDIKS SEAGATE 1 TB' => 'Harddisk Seagate 1TB',
            'SEAGATE 250GB' => 'Harddisk Seagate 250GB',
            'HARDIKS LAPTOP' => 'Harddisk Laptop (General)',
        ];
    }

    private function getRawData(): array
    {
        return [
            ['item' => 'TANG POTONG', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG LANCIP', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG BIASA', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'LAN TESTER', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TOOLKIT SATU SET', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'OBENG SET LAPTOP', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG CRIMPING', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'ALAT LEM TEMBAK', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'PISAU CUTTER', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'MULTI METER DIGITAL', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG CRIMPING', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'LAN TESTER', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'GUNTING KECIL', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'GERGAJI KECIL', 'location' => 'BT Store', 'qty' => 2],
            ['item' => 'OPTICAL POWER METER (OPM)', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'HARDISK EXTERNAL', 'location' => 'BT Store', 'qty' => 2],
            ['item' => 'OBENG SET 115', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'TANG BIASA KECIL', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'TANG LANCIP KECIL', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'TANG POTONG KECIL', 'location' => 'BT Store', 'qty' => 1],
            ['item' => '1 SET TOOLKIT OBENG PALU Dll', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'OBENG KUNING', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'FANVIL', 'location' => 'BT Store', 'qty' => 1],
            ['item' => 'SUNTIKAN BESAR', 'location' => 'BT Store', 'qty' => 3],
            ['item' => 'TANG CRIMPING', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'HARDIKS WD 500GB', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'HARDIKS SEAGATE 500GB', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'HARDIKS WD 320GB', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'HARDIKS SEAGATE 1 TB', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'SEAGATE 250GB', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'POWER SUPPLY TESTER', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'HARDIKS LAPTOP', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'TESTER LAN', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'POE', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'UPS', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'HT', 'location' => 'JMP', 'qty' => 3],
            ['item' => 'BLOWER', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'GUNTING', 'location' => 'TGS', 'qty' => 2],
            ['item' => 'OBENG', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'KATER', 'location' => 'TGS', 'qty' => 4],
            ['item' => 'BLOWER', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG POTONG', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG LANCIP', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG BIASA', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'STB', 'location' => 'JMP', 'qty' => 1],
        ];
    }
}
