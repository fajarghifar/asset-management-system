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
        // 1. Map Item Names from "Raw Data" to Product Codes
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
            'GUNTING' => 'GNTBS', // Added alias
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
            'TESTER LAN' => 'LANTST', // Added alias
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

        // 2. Map Location Alias to LocationSite Enum
        $locationAliasToSite = [
            'TGS' => LocationSite::TGS,
            'BT Store' => LocationSite::BT,
            'JMP' => LocationSite::JMP2, // Assuming JMP refers to JMP2
        ];

        // 3. Raw Data to Seed
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

        // --- OPTIMIZATION START ---

        // 1. Prefetch Products (Type Asset Only)
        // Ensure keys are array values of itemNameToCode to filter fetching
        $products = Product::whereIn('code', array_unique($itemNameToCode))
            ->where('type', ProductType::Asset)
            ->get()
            ->keyBy('code');

        if ($products->isEmpty()) {
            $this->command->error('No Asset products found. Run ProductSeeder first.');
            return;
        }

        // 2. Prefetch Locations
        $allLocations = Location::all();
        $locationLookup = [];

        foreach ($locationAliasToSite as $alias => $siteEnum) {
            $siteLocations = $allLocations->where('site', $siteEnum);

            // Logic: Prefer 'Ruang IT' or 'Gudang IT' or 'Kantor IT' if exists
            // Otherwise fallback to first location available.
            $preferred = $siteLocations->first(function($loc) {
                $name = strtolower($loc->name);
                return str_contains($name, 'it') || str_contains($name, 'server');
            });

            $fallback = $siteLocations->first();

            $locationLookup[$alias] = $preferred ?? $fallback;
        }

        $totalAssets = 0;
        $globalCounter = 1; // You might want this to be per product or global?
        // Usually Asset Tag sequence matches total assets or per type.
        // Example: AST-2026-TANGPT-0001
        // Let's reset counter per product if requested, but user snippet used globalCounter++?
        // No, user snippet: "sprintf(..., globalCounter++)" implies global unique sequence suffix.
        // But typically the suffix is per prefix. However, following user snippet logic.

        $now = now();
        $year = $now->format('y'); // 2 digits usually, user used Y (4 digits)
        // User format: AST-%s-%s-%04d (AST-2024-CODE-0001)

        $this->command->info("Starting Asset Seeding...");

        foreach ($rawData as $row) {
            $itemName = trim($row['item']);

            // Map Name to Code
            $productCode = $itemNameToCode[$itemName] ?? null;

            if (!$productCode) {
                // Try alias mapping if direct fail (e.g. TESTER LAN covered in itemNameToCode now)
                $this->command->warn("⚠️ SKIP: Item '$itemName' not mapped to any Product Code.");
                continue;
            }

            // Retrieve Product
            $product = $products->get($productCode);

            if (!$product) {
                // Could be product exists but not as Asset type
                // $this->command->warn("⚠️ SKIP: Product '$productCode' ($itemName) not found or not Asset type.");
                continue;
            }

            // Retrieve Location
            $locationAlias = $row['location'];
            $location = $locationLookup[$locationAlias] ?? null;
            $locationId = $location?->id;

            if (!$locationId) {
                $this->command->warn("⚠️ SKIP: Location '$locationAlias' not found.");
                continue;
            }

            $qty = (int) $row['qty'];

            if ($qty <= 0) continue;

            for ($i = 0; $i < $qty; $i++) {
                // Generate Tag
                // Format: AST-YEAR-CODE-GlobalSequence ? Or Product Sequence?
                // User snippet: globalCounter++
                // Let's query existing assets count? No, seeder usually starts fresh or appends.
                // Optimally we'd track per product counter.
                // But let's stick to user example of global counter:

                // Note: If running seeder multiple times, this might collide if not checking DB.
                // We will assume fresh seed or handle dupes (create throws error? no, we should catch unique)

                // Let's use uniqid or a better logic if this is intended for production.
                // For seeding, globalCounter is fine if we start high or assume empty DB.
                // I will add a random offset to globalCounter to avoid collision with existing data if possible,
                // or just increment.

                $assetTag = sprintf('AST-%s-%s-%04d', $now->format('Y'), $product->code, $globalCounter++);

                // Check collisions (quick check)
                while (Asset::where('asset_tag', $assetTag)->exists()) {
                    $assetTag = sprintf('AST-%s-%s-%04d', $now->format('Y'), $product->code, ++$globalCounter);
                }

                Asset::create([
                    'product_id'     => $product->id,
                    'location_id'    => $locationId,
                    'asset_tag'      => $assetTag,
                    'status'         => AssetStatus::InStock, // Default
                    'purchase_date'  => $now->subDays(rand(1, 730)), // Random date within 2 years
                    'notes'          => "Migrasi Asset (Asal: $locationAlias, Item: $itemName)",
                ]);
            }

            $totalAssets += $qty;
        }

        $this->command->info("🎉 ASSET SEEDER: Total Aset Fisik Dibuat: $totalAssets");
    }
}
