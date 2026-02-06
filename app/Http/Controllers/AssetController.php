<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\DTOs\AssetData;
use App\Models\Product;
use App\Models\Location;
use Illuminate\View\View;
use App\Enums\AssetStatus;
use App\Services\AssetService;
use App\Exceptions\AssetException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Assets\StoreAssetRequest;
use App\Http\Requests\Assets\UpdateAssetRequest;

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
        return view('assets.create');
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
        return view('assets.edit', [
            'asset' => $asset,
        ]);
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        try {
            if ($asset->image_path) {
                Storage::disk('public')->delete($asset->image_path);
            }

            $this->assetService->deleteAsset($asset);

            return redirect()->route('assets.index')
                ->with('success', __('Asset deleted successfully.'));

        } catch (AssetException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', __('Cannot delete asset. It may have related history records.'));
        }
    }
}
