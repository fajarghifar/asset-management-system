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
    public function run(): void
    {
        $itemNameToCode = [
            // Fixed Items
            'TANG POTONG' => 'TANGPT',
            'TANG LANCIP' => 'TANGLC',
            'TANG BIASA' => 'TANGBS',
            'TANG POTONG KECIL' => 'TPKCL',
            'TANG LANCIP KECIL' => 'TLKCL',
            'TANG BIASA KECIL' => 'TBKCL',
            'TANG CRIMPING' => 'CRIMP',
            'GUNTING BESAR' => 'GNTBS',
            'GUNTING KECIL' => 'GNTKC',
            'PISAU CUTTER' => 'CUTTER',
            'KATER' => 'CUTTER',
            'GERGAJI KECIL' => 'GERGAJ',
            'OBENG SET LAPTOP' => 'OBLPT',
            'OBENG SET 115' => 'OB115',
            'OBENG KUNING' => 'OBKNG',
            'OBENG' => 'OBSTD',
            'OBENG STANDAR' => 'OBSTD',
            'TOOLKIT SATU SET' => 'TOOLKT',
            '1 SET TOOLKIT OBENG PALU Dll' => 'TKPALU',
            'ALAT LEM TEMBAK' => 'GLUEGN',
            'BLOWER' => 'BLOWER',
            'SUNTIKAN BESAR' => 'SUNTIK',
            'LAN TESTER' => 'LANTST',
            'MULTI METER DIGITAL' => 'MULTI',
            'OPTICAL POWER METER (OPM)' => 'OPM',
            'POWER SUPPLY TESTER' => 'PSTEST',
            'MATHERPAS' => 'WTRPAS',
            'UPS' => 'UPS',
            'HT' => 'HT',
            'STB' => 'STB',
            'FANVIL' => 'IPPHON',
            'POE' => 'POE',

            // Installed Items
            'HARDISK EXTERNAL' => 'HDDEXT',
            'HARDIKS WD 500GB' => 'WD500',
            'HARDIKS SEAGATE 500GB' => 'SGT500',
            'HARDIKS WD 320GB' => 'WD320',
            'HARDIKS SEAGATE 1 TB' => 'SGT1TB',
            'SEAGATE 250GB' => 'SGT250',
            'HARDIKS LAPTOP' => 'HDDLAP',
        ];

        // Raw Data
        $rawData = [
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

        // Location Mapping
        $locationAliasToSite = [
            'TGS' => LocationSite::TGS,
            'BT Store' => LocationSite::BT,
            'JMP' => LocationSite::JMP2,
        ];

        // --- OPTIMIZATION START ---
        // 1. Prefetch Products (Type Asset Only to filter early)
        $products = Product::whereIn('code', array_values($itemNameToCode))
            ->where('type', ProductType::Asset)
            ->get()
            ->keyBy('code');

        // 2. Prefetch Locations
        // Logic match: 'Ruang IT' preference or just site fallback
        $allLocations = Location::all();
        $locationLookup = [];

        foreach ($locationAliasToSite as $alias => $siteEnum) {
            $siteLocations = $allLocations->where('site', $siteEnum);

            // Prefer 'Ruang IT' if exists, otherwise take first
            $preferred = $siteLocations->first(fn($loc) => str_contains($loc->name, 'Ruang IT'));
            $fallback = $siteLocations->first();

            $locationLookup[$alias] = $preferred ?? $fallback;
        }

        $totalAssets = 0;
        $globalCounter = 1;
        $now = now();

        foreach ($rawData as $row) {
            $itemName = trim($row['item']);

            // Fix Typo
            if ($itemName === 'TESTER LAN') $itemName = 'LAN TESTER';

            $locationAlias = $row['location'];
            $qty = (int) $row['qty'];

            if ($qty <= 0) continue;

            $productCode = $itemNameToCode[$itemName] ?? null;
            if (!$productCode) continue;

            // Use In-Memory Lookup
            $product = $products->get($productCode);

            if (!$product) {
                // If product not found in the fetched list, it might be Consumable or Missing
                // Since we filtered by Asset type above, this correctly skips Consumables
                // Or warns if Asset is missing.
                $this->command->warn("⚠️ SKIP: Produk '$productCode' ($itemName) tidak ditemukan atau bukan Aset.");
                continue;
            }

            $location = $locationLookup[$locationAlias] ?? null;
            $locationId = $location?->id;

            for ($i = 0; $i < $qty; $i++) {
                $assetTag = sprintf('AST-%s-%s-%04d', date('Y'), $product->code, $globalCounter++);

                // Prepare batch insert data (AssetObserver won't run on insert, but that's okay for Seeding)
                // However, user specifically asked for "Clean Code".
                // Using create() triggers Observers which is good for history.
                // Given the scale isn't massive (hundreds), create() loop is acceptable for "Correctness".
                // If massive (10k+), we'd use insert() and manual history.
                // Let's stick to create() for now to ensure AssetObserver registers the 'Register' history.

                Asset::create([
                    'product_id'     => $product->id,
                    'location_id' => $locationId,
                    'asset_tag'      => $assetTag,
                    'status'         => AssetStatus::InStock,
                    'purchase_date' => $now->subMonths(rand(1, 24)),
                    'purchase_price' => 0,
                    'notes'          => "Migrasi dari data lama (Lokasi Awal: $locationAlias)",
                ]);
            }

            $totalAssets += $qty;
        }

        $this->command->info("🎉 ASSET SEEDER: Total Aset Fisik Dibuat: $totalAssets");
    }
}
