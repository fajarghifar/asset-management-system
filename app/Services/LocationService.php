<?php

namespace App\Services;

use App\DTOs\LocationData;
use App\Models\Location;
use App\Exceptions\LocationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class LocationService
{
    /**
     * Create a new location.
     */
    public function createLocation(LocationData $data): Location
    {
        return DB::transaction(function () use ($data) {
            try {
                return Location::create($data->toArray());
            } catch (Throwable $e) {
                throw LocationException::creationFailed($e->getMessage(), $e);
            }
        });
    }

    /**
     * Update an existing location.
     */
    public function updateLocation(Location $location, LocationData $data): Location
    {
        return DB::transaction(function () use ($location, $data) {
            try {
                $location->update($data->toArray());
                return $location->refresh();
            } catch (Throwable $e) {
                throw LocationException::updateFailed((string) $location->id, $e->getMessage(), $e);
            }
        });
    }

    /**
     * Delete a location.
     */
    public function deleteLocation(Location $location): void
    {
        DB::transaction(function () use ($location) {
            try {
                if ($location->assets()->exists()) {
                    throw LocationException::inUse(__("Cannot delete location ':name' because it has associated assets.", ['name' => $location->name]));
                }

                if ($location->consumableStocks()->exists()) {
                    throw LocationException::inUse(__("Cannot delete location ':name' because it has associated consumable stocks.", ['name' => $location->name]));
                }

                $location->delete();
            } catch (LocationException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw LocationException::deletionFailed((string) $location->id, $e->getMessage(), $e);
            }
        });
    }

    /**
     * Get all locations.
     *
     * @return Collection<int, Location>
     */
    public function getAllLocations(): Collection
    {
        return Location::orderBy('code', 'asc')->get();
    }
}
