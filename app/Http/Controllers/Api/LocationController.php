<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->input('q') ?? $request->input('search') ?? $request->input('term');

        return Location::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('site', 'like', "%{$search}%");
                });
            })
            ->orderBy('site')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($location) {
                // Formatting "Site - Name"
                $siteLabel = $location->site->getLabel();
                return [
                    'value' => $location->id,
                    'text' => "{$location->code} | {$siteLabel} - {$location->name}",
                ];
            });
    }
}
