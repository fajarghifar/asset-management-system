<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Enums\ProductType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenSpout\Reader\XLSX\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductTemplateExport;
use Illuminate\Support\Facades\Validator;

class ProductImportController extends Controller
{
    public function create()
    {
        return view('products.import');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductTemplateExport, 'products_template.xlsx');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120', // 5MB Max
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $reader = new Reader();
        $reader->open($path);

        $stats = [
            'imported' => 0,
            'failed' => 0, // Counts rows that failed validation
            'skipped' => 0, // Counts rows with existing code (if we skip duplicates)
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            // Pre-fetch latest code sequence to minimize DB queries
            $year = date('Y');
            $prefix = "PRD.{$year}.";

            $latestProduct = Product::where('code', 'like', "{$prefix}%")
                ->orderByRaw('LENGTH(code) DESC')
                ->orderBy('code', 'desc')
                ->first();

            $nextNumber = 1;
            if ($latestProduct) {
                $lastCode = $latestProduct->code;
                $lastNumber = (int) str_replace($prefix, '', $lastCode);
                $nextNumber = $lastNumber + 1;
            }

            foreach ($reader->getSheetIterator() as $sheet) {
                // Only process the first sheet
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    if ($rowIndex === 1)
                        continue; // Skip header

                    $cells = $row->getCells();
                    $data = [];
                    foreach ($cells as $cell) {
                        $data[] = $cell->getValue();
                    }

                    // Columns: Code, Name, Type, Category, Loanable, Description
                    // If Name is empty, skip. Code can be empty now.
                    if (empty($data[1])) {
                        continue;
                    }

                    $code = $data[0] ?? null;

                    // Auto-generate code if empty
                    if (empty($code)) {
                        $code = "{$prefix}" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                        $nextNumber++;
                    }

                    $canBeLoaned = $data[4] ?? 1;
                    if (is_string($canBeLoaned)) {
                        $canBeLoaned = strtolower($canBeLoaned) === 'yes' ? true : (strtolower($canBeLoaned) === 'no' ? false : $canBeLoaned);
                    }

                    $type = $data[2] ?? null;
                    if (is_string($type)) {
                        $type = strtolower($type); // 'Asset' -> 'asset'
                    }

                    $rowData = [
                        'code' => $code,
                        'name' => $data[1] ?? null,
                        'type' => $type,
                        'category_name' => $data[3] ?? null,
                        'can_be_loaned' => $canBeLoaned,
                        'description' => $data[5] ?? null,
                    ];

                    // Validate
                    $validator = Validator::make($rowData, [
                        'code' => ['required', 'string', 'max:255'],
                        'name' => ['required', 'string', 'max:255'],
                        'type' => ['required', Rule::enum(ProductType::class)],
                        'category_name' => ['required', 'string'],
                        'can_be_loaned' => ['boolean'],
                    ]);

                    if ($validator->fails()) {
                        $stats['failed']++;
                        $stats['errors'][] = __("Row :row: :message", ['row' => $rowIndex, 'message' => implode(', ', $validator->errors()->all())]);
                        continue;
                    }

                    // Check if code exists (double check, though we auto-generated unique ones, manual ones might conflict)
                    if (Product::where('code', $rowData['code'])->exists()) {
                        $stats['skipped']++;
                        continue;
                    }

                    // Find or Create Category
                    $category = Category::firstOrCreate(
                        ['name' => $rowData['category_name']],
                        ['slug' => \Illuminate\Support\Str::slug($rowData['category_name'])]
                    );

                    Product::create([
                        'name' => $rowData['name'],
                        'code' => $rowData['code'],
                        'description' => $rowData['description'],
                        'type' => $rowData['type'],
                        'category_id' => $category->id,
                        'can_be_loaned' => $rowData['can_be_loaned'],
                    ]);

                    $stats['imported']++;
                }
                break; // Only process first sheet
            }

            DB::commit();
            $reader->close();

            $message = __("Import completed. Imported: :imported, Skipped: :skipped, Failed: :failed.", [
                'imported' => $stats['imported'],
                'skipped' => $stats['skipped'],
                'failed' => $stats['failed'],
            ]);

            if (!empty($stats['errors'])) {
                // Log detailed errors or show them?
                // For now, let's flash a few errors if any
                Log::warning('Import Errors', $stats['errors']);
                if (count($stats['errors']) > 0) {
                    $message .= __(" Check logs for details. First error: :error", ['error' => $stats['errors'][0]]);
                }
            }

            return redirect()->route('products.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $reader->close();
            Log::error('Import Failed', ['error' => $e->getMessage()]);
            return back()->with('error', __("Import failed: :message", ['message' => $e->getMessage()]));
        }
    }
}
