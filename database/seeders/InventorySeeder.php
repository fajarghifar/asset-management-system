<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Location;
use App\Models\ItemStock;
use Illuminate\Database\Seeder;
use App\Models\FixedItemInstance;
use App\Models\InstalledItemInstance;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Mapping eksplisit: [nama di data inventaris mentah => kode item di ItemSeeder]
        $itemNameToCode = [
            // --- Dari FIXED ---
            'TANG POTONG' => 'TANGPT',
            'TANG LANCIP' => 'TANGLC',
            'TANG BIASA' => 'TANGBS',
            'TANG POTONG KECIL' => 'TPKCL',
            'TANG LANCIP KECIL' => 'TLKCL',
            'TANG BIASA KECIL' => 'TBKCL',
            'CRIMPING' => 'CRIMP',
            'TANG CRIMPING' => 'CRIMP', // duplikat nama, tapi sama kode
            'GUNTING BESAR' => 'GNTBS',
            'GUNTING' => 'GNTBS', // "Gunting" di data = Gunting Besar
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
            'TESTER POWER SUPAY' => 'PSTEST', // typo
            'MATHERPAS' => 'WTRPAS',
            'UPS' => 'UPS',
            'HT' => 'HT',
            'STB' => 'STB',
            'FANVIL' => 'IPPHON',
            'POE' => 'POE',
            'HARDISK EXTERNAL' => 'HDDEXT',
            'HARDIKS WD 500GB' => 'WD500',
            'HARDIKS SEAGATE 500GB' => 'SGT500',
            'HARDIKS WD 320GB' => 'WD320',
            'HARDIKS SEAGATE 1 TB' => 'SGT1TB',
            'SEAGATE 250GB' => 'SGT250',
            'HARDIKS LAPTOP' => 'HDDLAP',
            'CLEANING KIT' => 'CLKIT',
            'TESTER LAN' => 'LANTST',
            'BATERAI CIMOS' => 'CMOS',
            'FIMEL' => 'FEMALE',
            'PASTA' => 'PASTA',
            'JACK DC MALE' => 'JACKDC',
            'KEYBOARD MINI' => 'KEYMIN',
            'MADHERBOARD' => 'MOBO',
            'HDMI TO USB' => 'CNHDMI',
            'VGA TO USB' => 'CNVGA',
            'LAMPU KEPALA' => 'HEADLP',
            'RG 4' => 'RG4',
            'KONEKTOR RJ45' => 'RJ45',
            'RJ11' => 'RJ11',
        ];

        // Data inventaris mentah (langsung dari tabel Anda)
        $rawData = [
            ['item' => 'TANG POTONG', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG LANCIP', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TANG BIASA', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'CLEANING KIT', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'LAN TESTER', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'TOOLKIT SATU SET', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'OBENG SET LAPTOP', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'CRIMPING', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'ALAT LEM TEMBAK', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'PISAU CUTTER', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'MULTI METER DIGITAL', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'CRIMPING', 'location' => 'BT Store', 'qty' => 1],
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
            ['item' => 'CRIMPING', 'location' => 'JMP', 'qty' => 2],
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
            ['item' => 'KONEKTOR RJ45', 'location' => 'JMP', 'qty' => 163],
            ['item' => 'BATERAI CIMOS', 'location' => 'JMP', 'qty' => 8],
            ['item' => 'MATHERPAS', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'RJ11', 'location' => 'JMP', 'qty' => 61],
            ['item' => 'FIMEL', 'location' => 'JMP', 'qty' => 23],
            ['item' => 'PASTA', 'location' => 'JMP', 'qty' => 5],
            ['item' => 'JACK DC MALE', 'location' => 'JMP', 'qty' => 27],
            ['item' => 'BLOWER', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'KEYBOARD MINI', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'MADHERBOARD', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'TESTER POWER SUPAY', 'location' => 'JMP', 'qty' => 1],
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

        // Mapping lokasi → area code
        $locationAliasToAreaCode = [
            'TGS' => 'TGS',
            'BT Store' => 'BT',
            'JMP' => 'JMP2',
        ];

        foreach ($rawData as $row) {
            $itemName = trim($row['item']);
            $locationAlias = $row['location'];
            $qty = (int) $row['qty'];

            if ($qty <= 0)
                continue;

            // Cari kode item berdasarkan nama persis di data mentah
            $itemCode = $itemNameToCode[$itemName] ?? null;

            if (!$itemCode) {
                $this->command->warn("⚠️ Tidak ada mapping untuk item: \"$itemName\"");
                continue;
            }

            // Cari item di DB berdasarkan kode
            $item = Item::where('code', $itemCode)->first();
            if (!$item) {
                $this->command->error("❌ Item dengan kode \"$itemCode\" (nama: \"$itemName\") tidak ditemukan di database. Pastikan ItemSeeder sudah dijalankan.");
                continue;
            }

            $itemType = $item->type;

            // Jika tidak ada lokasi (null), lewati atau log
            if ($locationAlias === null) {
                $this->command->info("ℹ️ Item tanpa lokasi: {$item->name} (qty: $qty)");
                continue;
            }

            // Cari area berdasarkan alias
            $areaCode = $locationAliasToAreaCode[$locationAlias] ?? null;
            if (!$areaCode) {
                $this->command->warn("⚠️ Alias lokasi tidak dikenali: \"$locationAlias\"");
                continue;
            }

            $area = Area::where('code', $areaCode)->first();
            if (!$area) {
                $this->command->warn("⚠️ Area tidak ditemukan: $areaCode");
                continue;
            }

            // Cari lokasi "Ruang IT" di area tersebut
            $location = Location::where('area_id', $area->id)
                ->where('name', 'Ruang IT')
                ->first();

            if (!$location) {
                $this->command->warn("⚠️ Lokasi 'Ruang IT' tidak ditemukan di area: {$area->name}");
                continue;
            }

            // Proses berdasarkan tipe
            if ($itemType->isConsumable()) {
                $stock = ItemStock::firstOrNew([
                    'item_id' => $item->id,
                    'location_id' => $location->id,
                ]);
                $stock->quantity = ($stock->quantity ?? 0) + $qty;
                $stock->min_quantity = $stock->min_quantity ?? 0;
                $stock->save();

            } elseif ($itemType->isFixed()) {
                for ($i = 0; $i < $qty; $i++) {
                    FixedItemInstance::create([
                        'item_id' => $item->id,
                        'location_id' => $location->id,
                        'status' => 'available',
                        'serial_number' => null,
                        'notes' => 'Auto-seeded',
                    ]);
                }

            } elseif ($itemType->isInstalled()) {
                for ($i = 0; $i < $qty; $i++) {
                    InstalledItemInstance::create([
                        'item_id' => $item->id,
                        'current_location_id' => $location->id,
                        'installed_at' => now()->subDays(rand(30, 365)),
                        'serial_number' => null,
                        'notes' => 'Auto-seeded',
                    ]);
                }
            }
        }

        $this->command->info("✅ InventorySeeder selesai.");
    }
}
