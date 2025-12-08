<?php

namespace App\Observers;

use App\Models\Area;
use App\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LocationObserver
{
    public function creating(Location $location): void
    {
        $area = $location->area ?? Area::find($location->area_id);

        if (!$area || blank($area->code)) {
            throw ValidationException::withMessages([
                'area_id' => 'Area tidak valid atau belum memiliki Kode.',
            ]);
        }

        $targetLength = 9;
        $separator = '-';

        $prefixLength = strlen($area->code) + strlen($separator);
        $availableSuffix = $targetLength - $prefixLength;

        if ($availableSuffix < 3) {
            throw ValidationException::withMessages([
                'area_id' => "Kode Area '{$area->code}' terlalu panjang. Mohon perpendek kode area atau hubungi IT.",
            ]);
        }

        $location->code = $this->generateUniqueCode($area->code, $separator, $availableSuffix);
    }

    private function generateUniqueCode(string $areaCode, string $separator, int $length): string
    {
        $maxAttempts = 50;
        $attempt = 0;

        do {
            $suffix = strtoupper(Str::random($length));
            $fullCode = $areaCode . $separator . $suffix;
            $exists = Location::where('code', $fullCode)->exists();
            $attempt++;

            if ($attempt > $maxAttempts) {
                throw ValidationException::withMessages(['code' => 'Gagal generate kode unik (Timeout).']);
            }
        } while ($exists);

        return $fullCode;
    }

    public function deleting(Location $location): void
    {
        if ($location->hasConsumableStock()) {
            throw ValidationException::withMessages([
                'location' => "Gagal: Masih ada stok barang (Consumable) yang tersimpan di lokasi '{$location->name}'.",
            ]);
        }

        if ($location->hasFixedItems()) {
            throw ValidationException::withMessages([
                'location' => "Gagal: Lokasi '{$location->name}' masih digunakan oleh Aset Tetap (Fixed Items).",
            ]);
        }

        if ($location->hasInstalledItems()) {
            throw ValidationException::withMessages([
                'location' => "Gagal: Lokasi '{$location->name}' masih digunakan oleh Aset Terpasang (Installed Items).",
            ]);
        }
    }
}
