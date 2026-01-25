<?php

namespace App\Http\Controllers;

use App\Models\Kit;
use App\DTOs\KitData;
use App\Services\KitService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Kits\StoreKitRequest;
use App\Http\Requests\Kits\UpdateKitRequest;

class KitController extends Controller
{
    public function __construct(
        protected KitService $kitService
    ) {}

    public function index()
    {
        return view('kits.index');
    }

    public function create()
    {
        return view('kits.create');
    }

    public function store(StoreKitRequest $request)
    {
        $data = KitData::fromArray($request->validated());
        $this->kitService->createKit($data);

        return redirect()->route('kits.index')->with('success', 'Kit created successfully.');
    }

    public function show(Kit $kit)
    {
        $kit->load(['items.product', 'items.location']);
        $availability = $this->kitService->getKitAvailability($kit);
        return view('kits.show', compact('kit', 'availability'));
    }

    public function edit(Kit $kit)
    {
        $kit->load('items.product');
        return view('kits.edit', compact('kit'));
    }

    public function update(UpdateKitRequest $request, Kit $kit)
    {
        $data = KitData::fromArray($request->validated());
        $this->kitService->updateKit($kit, $data);

        return redirect()->route('kits.index')->with('success', 'Kit updated successfully.');
    }

    public function destroy(Kit $kit)
    {
        $this->kitService->deleteKit($kit);
        return redirect()->route('kits.index')->with('success', 'Kit deleted successfully.');
    }

    /**
     * Resolve a kit into loan items.
     */
    public function resolve(Request $request, Kit $kit): JsonResponse
    {
        $request->validate([
            'location_id' => 'nullable|exists:locations,id',
        ]);

        $resolvedItems = $this->kitService->resolveKitToLoanItems(
            $kit,
            $request->location_id ? (int) $request->location_id : null
        );

        return response()->json([
            'items' => $resolvedItems,
            'message' => count($resolvedItems) > 0
                ? 'Kit loaded successfully.'
                : 'No available items found for this kit in the selected location.',
        ]);
    }
}
