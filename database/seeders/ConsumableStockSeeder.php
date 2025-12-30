<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Location;
use App\Enums\ProductType;
use App\Enums\LocationSite;
use App\Models\ConsumableStock;
use Illuminate\Database\Seeder;

class ConsumableStockSeeder extends Seeder
{
    public function run(): void
    {
        $itemNameToCode = [
            // Consumable Items
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

        // Raw Data
        $rawData = [
            ['item' => 'CLEANING KIT', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'KONEKTOR RJ45', 'location' => 'JMP', 'qty' => 163],
            ['item' => 'BATERAI CIMOS', 'location' => 'JMP', 'qty' => 8],
            ['item' => 'MATHERPAS', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'RJ11', 'location' => 'JMP', 'qty' => 61],
            ['item' => 'PASTA', 'location' => 'JMP', 'qty' => 5],
            ['item' => 'JACK DC FEMALE', 'location' => 'JMP', 'qty' => 23],
            ['item' => 'JACK DC MALE', 'location' => 'JMP', 'qty' => 27],
            ['item' => 'KEYBOARD MINI', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'MADHERBOARD', 'location' => 'JMP', 'qty' => 1], // Treated as consumable per legacy comment
            ['item' => 'HDMI TO USB', 'location' => 'JMP', 'qty' => 1],
            ['item' => 'VGA TO USB', 'location' => 'JMP', 'qty' => 4],
            ['item' => 'LAMPU KEPALA', 'location' => 'JMP', 'qty' => 2],
            ['item' => 'RG 4', 'location' => 'JMP', 'qty' => 1],
        ];

        // Location Mapping
        $locationAliasToSite = [
            'TGS' => LocationSite::TGS,
            'BT Store' => LocationSite::BT,
            'JMP' => LocationSite::JMP2,
        ];

        $totalConsumables = 0;

        foreach ($rawData as $row) {
            $itemName = trim($row['item']);
            $locationAlias = $row['location'];
            $qty = (int) $row['qty'];

            if ($qty <= 0) continue;

            $productCode = $itemNameToCode[$itemName] ?? null;
            if (!$productCode) continue;

            $product = Product::where('code', $productCode)->first();
            if (!$product) {
                // $this->command->error("❌ ERROR: Produk '$productCode' ($itemName) tidak ditemukan.");
                continue;
            }

            if ($product->type !== ProductType::Consumable) {
                continue;
            }

            $location = null;
            if ($locationAlias) {
                $siteEnum = $locationAliasToSite[$locationAlias] ?? null;
                if ($siteEnum) {
                    $location = Location::where('site', $siteEnum)
                        ->where('name', 'like', '%Ruang IT%')
                        ->first()
                        ?? Location::where('site', $siteEnum)->first();
                }
            }

            if (!$location) continue;

            $stock = ConsumableStock::where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->first();

            if ($stock) {
                $stock->quantity += $qty;
                $stock->save();
            } else {
                ConsumableStock::create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity' => $qty,
                    'min_quantity' => 5,
                ]);
            }

            $totalConsumables += $qty;
        }

        $this->command->info("🎉 CONSUMABLE SEEDER: Total Stok Ditambahkan: $totalConsumables");
    }
}
