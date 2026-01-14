<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Product;
use App\Models\Location;
use App\Enums\AssetStatus;
use App\Services\AssetService;
use App\Http\Requests\Assets\StoreAssetRequest;
use App\Http\Requests\Assets\UpdateAssetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function __construct(
        protected AssetService $assetService
    ) {}

    public function index(): View
    {
        return view('assets.index');
    }

    public function create(): View
    {
        // Minimal data needed for dropdowns
        $products = Product::orderBy('name')->get(['id', 'name', 'code']);
        $locations = Location::orderBy('name')->get();
        // Passing statuses for dropdown
        return view('assets.create', [
            'products' => $products,
            'locations' => $locations,
            'statuses' => AssetStatus::cases(),
        ]);
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('assets', 'public');
        }

        $asset = $this->assetService->createAsset($data);

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Asset created successfully.');
    }

    public function show(Asset $asset): View
    {
        $asset->load(['product', 'location']);

        return view('assets.show', [
            'asset' => $asset,
        ]);
    }

    public function edit(Asset $asset): View
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'code']);
        $locations = Location::orderBy('name')->get();

        return view('assets.edit', [
            'asset' => $asset,
            'products' => $products,
            'locations' => $locations,
            'statuses' => AssetStatus::cases(),
        ]);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_path')) {
            // Delete old image if exists
            if ($asset->image_path) {
                Storage::disk('public')->delete($asset->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('assets', 'public');
        }

        $this->assetService->updateAsset($asset, $data);

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        try {
            if ($asset->image_path) {
                Storage::disk('public')->delete($asset->image_path);
            }

            $this->assetService->deleteAsset($asset);

            return redirect()->route('assets.index')
                ->with('success', 'Asset deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete asset. It may have related history records.');
        }
    }
}
