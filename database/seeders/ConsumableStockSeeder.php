<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Location;
use App\Enums\ProductType;
use App\Enums\LocationSite;
use App\Models\ConsumableStock;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ConsumableStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locationAliasToSite = [
            'TGS' => LocationSite::TGS,
            'BT Store' => LocationSite::BT,
            'JMP' => LocationSite::JMP2,
        ];

        // Gather all item names from raw data
        $rawData = $this->getRawData();
        $itemNames = array_column($rawData, 'item');

        // Prefetch Products by Name
        $products = Product::whereIn('name', $itemNames)
            ->where('type', ProductType::Consumable)
            ->get()
            ->keyBy('name');

        if ($products->isEmpty()) {
            $this->command->error('No Consumable products found. Run ProductSeeder first.');
            return;
        }

        $allLocations = Location::all();
        $locationLookup = $this->resolveLocations($allLocations, $locationAliasToSite);

        $totalConsumables = 0;

        foreach ($rawData as $row) {
            $itemName = $row['item'];

            if (!$products->has($itemName)) {
                $this->command->warn("⚠️ Product not found: $itemName");
                continue;
            }

            $location = $locationLookup[$row['location']] ?? null;
            if (!$location) {
                $this->command->warn("⚠️ Location skipped: {$row['location']}");
                continue;
            }

            $qty = (int) $row['qty'];
            if ($qty <= 0)
                continue;

            $product = $products->get($itemName);

            // Upsert / Increment Stock
            $stock = ConsumableStock::firstOrNew([
                'product_id' => $product->id,
                'location_id' => $location->id,
            ]);

            $stock->quantity = ($stock->quantity ?? 0) + $qty;
            $stock->min_quantity = $stock->min_quantity ?? 5;
            $stock->save();

            $totalConsumables += $qty;
        }

        $this->command->info("🎉 CONSUMABLE SEEDER FINISHED");
        $this->command->info("   - Total Qty Added : $totalConsumables");
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

    private function getRawData(): array
    {
        return [
            ['item' => 'Cleaning Kit', 'location' => 'TGS', 'qty' => 1],
            ['item' => 'Konektor RJ45', 'location' => 'JMP', 'qty' => 163],
            ['item' => 'Baterai CMOS 2032', 'location' => 'JMP', 'qty' => 8],
            ['item' => 'Konektor RJ11', 'location' => 'JMP', 'qty' => 61],
            ['item' => 'Pasta Processor (Thermal Paste)', 'location' => 'JMP', 'qty' => 5],
            ['item' => 'Konektor Female', 'location' => 'JMP', 'qty' => 23],
            ['item' => 'Jack DC Male', 'location' => 'JMP', 'qty' => 27],
            ['item' => 'Kabel RG4 / Coaxial', 'location' => 'JMP', 'qty' => 1],
        ];
    }
}
