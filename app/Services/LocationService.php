<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class LocationService
{
    /**
     * Create a new location with transaction safety.
     *
     * @param array $data
     * @return Location
     * @throws \Exception
     */
    public function createLocation(array $data): Location
    {
        return DB::transaction(function () use ($data) {
            try {
                $location = Location::create($data);

                Log::info("Location created: ID {$location->id} by User " . Auth::id());

                return $location;
            } catch (\Exception $e) {
                Log::error("Failed to create location: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update an existing location with transaction safety.
     *
     * @param Location $location
     * @param array $data
     * @return Location
     * @throws \Exception
     */
    public function updateLocation(Location $location, array $data): Location
    {
        return DB::transaction(function () use ($location, $data) {
            try {
                $location->update($data);

                Log::info("Location updated: ID {$location->id} by User " . Auth::id());

                return $location;
            } catch (\Exception $e) {
                Log::error("Failed to update location ID {$location->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete a location safely.
     *
     * @param Location $location
     * @return bool
     * @throws \Exception
     */
    public function deleteLocation(Location $location): bool
    {
        return DB::transaction(function () use ($location) {
            try {
                $location->delete();

                Log::info("Location deleted: ID {$location->id} by User " . Auth::id());

                return true;
            } catch (\Exception $e) {
                Log::error("Failed to delete location ID {$location->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }
}
