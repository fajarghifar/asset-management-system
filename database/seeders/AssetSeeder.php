<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Product;
use App\Models\Location;
use App\Enums\AssetStatus;
use App\Enums\ProductType;
use App\Enums\LocationSite;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $itemNameToCode = [
            // --- Fixed Items ---
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

            // --- Installed Items (Sparepart - Kini masuk Asset) ---
            'HARDISK EXTERNAL' => 'HDDEXT',
            'HARDIKS WD 500GB' => 'WD500',
            'HARDIKS SEAGATE 500GB' => 'SGT500',
            'HARDIKS WD 320GB' => 'WD320',
            'HARDIKS SEAGATE 1 TB' => 'SGT1TB',
            'SEAGATE 250GB' => 'SGT250',
            'HARDIKS LAPTOP' => 'HDDLAP',

            // --- Consumable Items (Mapping tetap ada agar tidak error lookup, tapi nanti diskip) ---
            'CLEANING KIT' => 'CLKIT',
            'BATERAI CIMOS' => 'CMOS',
            'JACK DC FEMALE' => 'JACKFM',
            'JACK DC MALE' => 'JACKML',
            'PASTA' => 'PASTA',
            'KEYBOARD MINI' => 'KEYMIN',
            'MADHERBOARD' => 'MOBO',
            'HDMI TO USB' => 'CNHDMI',
            'VGA TO USB' => 'CNVGA',
            'LAMPU KEPALA' => 'HEADLP',
            'RG 4' => 'RG4',
            'KONEKTOR RJ45' => 'RJ45',
            'RJ11' => 'RJ11',
        ];

        // 2. Data Mentah
        $rawData = [
            ['item' => 'TANG POTONG', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG LANCIP', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG BIASA', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'CLEANING KIT', 'location' => 'TGS', 'qty' => 1],
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
            ['item' => 'POWER SUPPLY TESTER', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'HARDIKS LAPTOP', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'TESTER LAN', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'POE', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'UPS', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'HT', 'location' => 'JMP', 'qty' => 3],
            // Consumables (Akan diskip)
            ['item' => 'KONEKTOR RJ45', 'location' => 'JMP', 'qty' => 163],
            ['item' => 'BATERAI CIMOS', 'location' => 'JMP', 'qty' => 8],
            ['item' => 'MATHERPAS', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'RJ11', 'location' => 'JMP', 'qty' => 61],
            ['item' => 'PASTA', 'location' => 'JMP', 'qty' => 5],
            ['item' => 'JACK DC FEMALE', 'location' => 'JMP', 'qty' => 23],
            ['item' => 'JACK DC MALE', 'location' => 'JMP', 'qty' => 27],
            // End Consumables
            ['item' => 'BLOWER', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'KEYBOARD MINI', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'MADHERBOARD', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'POWER SUPPLY TESTER', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'HDMI TO USB', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'VGA TO USB', 'location' => 'JMP', 'qty' => 4],
            ['item' => 'GUNTING', 'location' => 'TGS', 'qty' => 2],
            ['item' => 'OBENG', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG CRIMPING', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'KATER', 'location' => 'TGS', 'qty' => 4],
            ['item' => 'BLOWER', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG POTONG', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG LANCIP', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG BIASA', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'LAN TESTER', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'MULTI METER DIGITAL', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'STB', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'LAMPU KEPALA', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'RG 4', 'location' => 'JMP', 'qty' => 1],
        ];

        // 3. Mapping Lokasi Lama -> Enum LocationSite Baru
        $locationAliasToSite = [
            'TGS' => LocationSite::TGS,
            'BT Store' => LocationSite::BT,
            'JMP' => LocationSite::JMP2,
        ];

        $totalAssets = 0;
        $globalCounter = 1; // Untuk generate Asset Tag unik

        foreach ($rawData as $row) {
            $itemName = trim($row['item']);
            $locationAlias = $row['location'];
            $qty = (int) $row['qty'];

            if ($qty <= 0) continue;

            // A. Cari Produk
            $productCode = $itemNameToCode[$itemName] ?? null;
            if (!$productCode) {
                // $this->command->warn("⚠️ SKIP: Mapping tidak ditemukan untuk '$itemName'");
                continue;
            }

            $product = Product::where('code', $productCode)->first();
            if (!$product) {
                $this->command->error("❌ ERROR: Produk '$productCode' ($itemName) tidak ditemukan di database.");
                continue;
            }

            // B. FILTER UTAMA: Skip Consumables
            if ($product->type === ProductType::Consumable) {
                // Opsional: Uncomment jika ingin melihat log apa saja yang diskip
                // $this->command->info("ℹ️ SKIP Consumable: {$product->name}");
                continue;
            }

            // C. Cari Lokasi (Menggunakan Logic Single Table + Enum Site)
            $location = null;
            if ($locationAlias) {
                $siteEnum = $locationAliasToSite[$locationAlias] ?? null;

                if ($siteEnum) {
                    // Cari "Ruang IT" di Site tersebut sebagai default
                    // Jika tidak ada, ambil sembarang lokasi di Site tersebut
                    $location = Location::where('site', $siteEnum)
                        ->where('name', 'like', '%Ruang IT%')
                        ->first();

                    if (!$location) {
                        $location = Location::where('site', $siteEnum)->first();
                    }
                }
            }

            // D. Generate Assets (Looping sebanyak Qty karena Asset perlu tag unik per unit)
            for ($i = 0; $i < $qty; $i++) {
                // Format Asset Tag: AST-{TAHUN}-{KODE_PRODUK}-{COUNTER}
                // Contoh: AST-2025-LANTST-0001
                $assetTag = sprintf(
                    'AST-%s-%s-%04d',
                    date('Y'),
                    $product->code,
                    $globalCounter++
                );

                Asset::create([
                    'product_id'     => $product->id,
                    'location_id'    => $location?->id, // Bisa null jika lokasi tidak ditemukan
                    'asset_tag'      => $assetTag,
                    'serial_number'  => null, // Kosongkan dulu, nanti update pas opname
                    'status'         => AssetStatus::InStock,
                    'purchase_date'  => now()->subMonths(rand(1, 24)), // Dummy date
                    'purchase_price' => 0,
                    'notes'          => "Migrasi dari data lama (Lokasi Awal: $locationAlias)",
                ]);
            }

            $totalAssets += $qty;
            $locationName = $location?->full_name ?? 'No Location';
            $this->command->info("✅ Created {$qty} unit(s) of [{$product->code}] {$product->name} -> {$locationName}");
        }

        $this->command->info("🎉 SEEDER SELESAI! Total Aset Fisik (Non-Consumable) Dibuat: $totalAssets");
    }
}
