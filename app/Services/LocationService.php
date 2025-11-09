<?php
namespace App\Services;

use App\Models\Location;
use Illuminate\Validation\ValidationException;

class LocationService
{
    /**
     * Periksa apakah lokasi bisa dihapus.
     */
    public function canDelete(Location $location): bool
    {
        return !(
            $location->itemStocks()->exists() ||
            $location->fixedItemInstances()->exists() ||
            $location->installedItemInstances()->exists()
        );
    }

    /**
     * Dapatkan pesan error jika tidak bisa dihapus.
     */
    public function getErrorMessage(Location $location): string
    {
        $errors = [];

        if ($location->itemStocks()->exists()) {
            $errors[] = 'Masih digunakan dalam stok barang habis pakai.';
        }

        if ($location->fixedItemInstances()->exists()) {
            $errors[] = 'Masih digunakan sebagai lokasi barang tetap.';
        }

        if ($location->installedItemInstances()->exists()) {
            $errors[] = 'Masih digunakan sebagai lokasi pemasangan barang terpasang.';
        }

        return 'Tidak bisa menghapus lokasi: ' . implode(' ', $errors);
    }

    /**
     * Hapus lokasi (soft delete).
     */
    public function delete(Location $location): void
    {
        if (!$this->canDelete($location)) {
            throw ValidationException::withMessages([
                'location' => $this->getErrorMessage($location),
            ]);
        }

        $location->delete();
    }
}
