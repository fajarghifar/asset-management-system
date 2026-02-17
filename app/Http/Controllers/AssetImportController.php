<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Asset;
use App\DTOs\AssetData;
use App\Models\Product;
use App\Models\Location;
use App\Enums\AssetStatus;
use Illuminate\Http\Request;
use App\Services\AssetService;
use Illuminate\Validation\Rule;
use OpenSpout\Reader\XLSX\Reader;
use Illuminate\Support\Facades\Log;
use App\Exports\AssetTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class AssetImportController extends Controller
{
    public function __construct(
        protected AssetService $assetService
    ) {}

    public function create()
    {
        return view('assets.import');
    }

    public function downloadTemplate()
    {
        return Excel::download(new AssetTemplateExport, 'assets_template.xlsx');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120', // 5MB Max
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $reader = new Reader();
        try {
            $reader->open($path);
        } catch (\Exception $e) {
            return back()->with('error', 'Could not open file: ' . $e->getMessage());
        }

        $stats = [
            'imported' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Best Effort Commit: Import valid rows, valid assets. Log failed rows.

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    if ($rowIndex === 1) continue; // Skip header

                    $cells = $row->getCells();
                    $data = [];
                    foreach ($cells as $cell) {
                        $data[] = $cell->getValue();
                    }

                    // Columns:
                    // 0: Asset Tag
                    // 1: Product (Code | Name)
                    // 2: Location (Code | Name)
                    // 3: Serial Number
                    // 4: Status
                    // 5: Purchase Date
                    // 6: Purchase Price
                    // 7: Notes

                    $assetTag = $data[0] ?? null;
                    // Parse "Code | Name" -> take Code
                    $productCodeRaw = $data[1] ?? null;
                    $locationCodeRaw = $data[2] ?? null;

                    $productCode = $productCodeRaw ? trim(explode('|', $productCodeRaw)[0]) : null;
                    $locationCode = $locationCodeRaw ? trim(explode('|', $locationCodeRaw)[0]) : null;

                    // Skip empty rows
                    if (empty($assetTag) && (empty($productCode) || empty($locationCode))) {
                        continue;
                    }

                    // Map Status
                    $statusString = !empty($data[4]) ? $data[4] : 'In Stock';
                    $status = $this->mapStatus($statusString);

                    // Parse Date
                    $purchaseDate = null;
                    if (!empty($data[5])) {
                        if ($data[5] instanceof \DateTime) {
                            $purchaseDate = $data[5];
                        } else {
                            try {
                                $purchaseDate = Carbon::parse($data[5]);
                            } catch (\Exception $e) {
                                $purchaseDate = null;
                            }
                        }
                    }

                    // Purchase Price
                    $purchasePrice = isset($data[6]) ? (float) $data[6] : 0;
                    $notes = isset($data[7]) && trim($data[7]) !== '' ? trim($data[7]) : null;
                    $serialNumber = isset($data[3]) && trim($data[3]) !== '' ? trim($data[3]) : null;

                    $rowData = [
                        'asset_tag' => $assetTag,
                        'product_code' => $productCode,
                        'location_code' => $locationCode,
                        'serial_number' => $serialNumber,
                        'status' => $status,
                        'purchase_date' => $purchaseDate,
                        'purchase_price' => $purchasePrice, // Pass to DTO
                        'notes' => $notes,
                    ];

                    $validator = Validator::make($rowData, [
                        'asset_tag' => ['nullable', 'string'], // Nullable for auto-gen
                        'product_code' => ['required', 'string', 'exists:products,code'],
                        'location_code' => ['required', 'string', 'exists:locations,code'],
                        'status' => ['required', Rule::enum(AssetStatus::class)],
                        'serial_number' => ['nullable', 'string'],
                        'purchase_date' => ['nullable', 'date'],
                        'purchase_price' => ['nullable', 'numeric', 'min:0'],
                        'notes' => ['nullable', 'string'],
                    ]);

                    if ($validator->fails()) {
                        $stats['failed']++;
                        $stats['errors'][] = "Row {$rowIndex}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    // Resolve IDs
                    $product = Product::where('code', $rowData['product_code'])->first();
                    $location = Location::where('code', $rowData['location_code'])->first();



                    // Update or Create
                    try {
                        if (!empty($rowData['asset_tag'])) {
                            // Update logic
                            $asset = Asset::where('asset_tag', $rowData['asset_tag'])->first();
                            if ($asset) {
                                $assetData = new AssetData(
                                    product_id: $product->id,
                                    location_id: $location->id,
                                    asset_tag: $rowData['asset_tag'],
                                    serial_number: $rowData['serial_number'],
                                    status: $rowData['status'], // Enum is resolved
                                    purchase_date: $rowData['purchase_date'] ? Carbon::instance($rowData['purchase_date'])->format('Y-m-d') : null,
                                    notes: $rowData['notes'],
                                    image_path: $asset->image_path, // Keep existing
                                    history_notes: "Bulk Import Update"
                                );
                                $this->assetService->updateAsset($asset, $assetData);
                                $stats['imported']++;
                            } else {
                                // Tag provided but not found -> Create with that tag
                                $assetData = new AssetData(
                                    product_id: $product->id,
                                    location_id: $location->id,
                                    asset_tag: $rowData['asset_tag'],
                                    serial_number: $rowData['serial_number'],
                                    status: $rowData['status'],
                                    purchase_date: $rowData['purchase_date'] ? Carbon::instance($rowData['purchase_date'])->format('Y-m-d') : null,
                                    notes: $rowData['notes'],
                                    image_path: null,
                                    history_notes: "Initial Import (with provided tag)"
                                );
                                $this->assetService->createAsset($assetData);
                                $stats['imported']++;
                            }

                        } else {
                            // Create (Auto Tag)
                            $assetData = new AssetData(
                                product_id: $product->id,
                                location_id: $location->id,
                                asset_tag: null, // Service handles generation
                                serial_number: $rowData['serial_number'],
                                status: $rowData['status'],
                                purchase_date: $rowData['purchase_date'] ? Carbon::instance($rowData['purchase_date'])->format('Y-m-d') : null,
                                notes: $rowData['notes'],
                                image_path: null,
                                history_notes: "Initial Import (Auto Tag)"
                            );
                            $this->assetService->createAsset($assetData);
                            $stats['imported']++;
                        }
                    } catch (\Exception $e) {
                        $stats['failed']++;
                        $stats['errors'][] = "Row {$rowIndex}: " . $e->getMessage();
                        Log::error($e);
                    }
                }
                break;
            }

            $reader->close();

            $message = "Import completed. Imported: {$stats['imported']}, Failed: {$stats['failed']}.";
            if (!empty($stats['errors'])) {
                Log::warning('Asset Import Errors', $stats['errors']);
                if (count($stats['errors']) > 0) {
                    $message .= " First error: " . $stats['errors'][0];
                }
            }

            return redirect()->route('assets.index')->with('success', $message);

        } catch (\Exception $e) {
            $reader->close();
            Log::error('Asset Import Failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function mapStatus( mixed $status): AssetStatus
    {
        if ($status instanceof AssetStatus) {
            return $status;
        }

        $string = strtolower((string)$status);

        // Map common variations
        return match ($string) {
            'loaned', 'loan' => AssetStatus::Loaned,
            'installed' => AssetStatus::Installed,
            'maintenance', 'under maintenance' => AssetStatus::Maintenance,
            'broken', 'damaged' => AssetStatus::Broken,
            'lost', 'missing' => AssetStatus::Lost,
            'disposed' => AssetStatus::Disposed,
            default => AssetStatus::InStock, // Default to InStock
        };
    }
}
