<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\ConsumableStock;
use OpenSpout\Reader\XLSX\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\ConsumableStockTemplateExport;

class ConsumableStockImportController extends Controller
{
    public function create()
    {
        return view('stocks.import');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ConsumableStockTemplateExport, 'stocks_template.xlsx');
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
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    if ($rowIndex === 1) { // Skip header
                        continue;
                    }

                    $cells = $row->getCells();
                    $data = [];
                    foreach ($cells as $cell) {
                        $data[] = $cell->getValue();
                    }

                    // Code Product, Name Product, Code Location, Name Location, Quantity, Min Qty
                    // Columns:
                    // 0: Product (Code | Name)
                    // 1: Location (Code | Site - Name)
                    // 2: Quantity
                    // 3: Min Quantity

                    $productCodeRaw = $data[0] ?? null;
                    $locationCodeRaw = $data[1] ?? null;

                    $productCode = $productCodeRaw ? trim(explode('|', $productCodeRaw)[0]) : null;
                    $locationCode = $locationCodeRaw ? trim(explode('|', $locationCodeRaw)[0]) : null;

                    if (empty($productCode) || empty($locationCode)) {
                        continue;
                    }

                    $rowData = [
                        'product_code' => $productCode,
                        'location_code' => $locationCode,
                        'quantity' => isset($data[2]) ? (int) $data[2] : 0,
                        'min_quantity' => isset($data[3]) ? (int) $data[3] : 0,
                    ];

                    $validator = Validator::make($rowData, [
                        'product_code' => ['required', 'string', 'exists:products,code'],
                        'location_code' => ['required', 'string', 'exists:locations,code'],
                        'quantity' => ['required', 'integer', 'min:0'],
                        'min_quantity' => ['required', 'integer', 'min:0'],
                    ]);

                    if ($validator->fails()) {
                        $stats['failed']++;
                        $stats['errors'][] = "Row {$rowIndex}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    // Resolve Product and Location
                    $product = Product::where('code', $rowData['product_code'])->first();
                    $location = Location::where('code', $rowData['location_code'])->first();

                    if (!$product) {
                        // Should be caught by validator but double check
                        $stats['failed']++;
                        continue;
                    }

                    if (!$location) {
                        $stats['failed']++;
                        $stats['errors'][] = "Row {$rowIndex}: Location code '{$rowData['location_code']}' not found.";
                        continue;
                    }

                    // Update or Create
                    $stock = ConsumableStock::where('product_id', $product->id)
                        ->where('location_id', $location->id)
                        ->first();

                    if ($stock) {
                        $stock->update([
                            'quantity' => $rowData['quantity'],
                            'min_quantity' => $rowData['min_quantity'],
                        ]);
                        $stats['updated']++;
                    } else {
                        ConsumableStock::create([
                            'product_id' => $product->id,
                            'location_id' => $location->id,
                            'quantity' => $rowData['quantity'],
                            'min_quantity' => $rowData['min_quantity'],
                        ]);
                        $stats['imported']++;
                    }
                }
                break;
            }

            DB::commit();
            $reader->close();

            $message = __('Import completed. Imported (New): :imported, Updated: :updated, Failed: :failed.', [
                'imported' => $stats['imported'],
                'updated' => $stats['updated'],
                'failed' => $stats['failed'],
            ]);
            if (!empty($stats['errors'])) {
                Log::warning('Stock Import Errors', $stats['errors']);
                if (count($stats['errors']) > 0) {
                    $message .= " " . __('First error: :error', ['error' => $stats['errors'][0]]);
                }
            }

            return redirect()->route('stocks.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $reader->close();
            Log::error('Stock Import Failed', ['error' => $e->getMessage()]);
            return back()->with('error', __('Import failed: :message', ['message' => $e->getMessage()]));
        }
    }
}
