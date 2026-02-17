<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Enums\LocationSite;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenSpout\Reader\XLSX\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\LocationTemplateExport;

class LocationImportController extends Controller
{
    public function create()
    {
        return view('locations.import');
    }

    public function downloadTemplate()
    {
        return Excel::download(new LocationTemplateExport, 'locations_template.xlsx');
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
            return back()->with('error', __('Could not open file: :message', ['message' => $e->getMessage()]));
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

                    // Columns: Code, Name, Site (Value - Label), Description
                    // 0: Code
                    // 1: Name
                    // 2: Site
                    // 3: Description

                    $code = $data[0] ?? null;
                    if (empty($code)) {
                        continue; // Skip empty rows or missing code
                    }

                    $name = $data[1] ?? null;
                    $siteRaw = $data[2] ?? null;
                    $description = $data[3] ?? null;

                    // Parse Site: "BT - Batik Trusmi" -> "BT"
                    $siteValue = $siteRaw;
                    if ($siteRaw && str_contains($siteRaw, ' - ')) {
                         $parts = explode(' - ', $siteRaw);
                         $siteValue = trim($parts[0]);
                    }

                    $rowData = [
                        'code' => $code,
                        'name' => $name,
                        'site' => $siteValue,
                        'description' => $description,
                    ];

                    $validator = Validator::make($rowData, [
                        'code' => ['required', 'string', 'max:50'], // Check max length
                        'name' => ['required', 'string', 'max:255'],
                        'site' => ['required', Rule::enum(LocationSite::class)],
                        'description' => ['nullable', 'string'],
                    ]);

                    if ($validator->fails()) {
                        $stats['failed']++;
                        $stats['errors'][] = "Row {$rowIndex}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    // Update or Create
                    $location = Location::where('code', $rowData['code'])->first();

                    if ($location) {
                        $location->update([
                            'name' => $rowData['name'],
                            'site' => $rowData['site'],
                            'description' => $rowData['description'],
                        ]);
                        $stats['updated']++;
                    } else {
                        Location::create([
                            'code' => $rowData['code'],
                            'name' => $rowData['name'],
                            'site' => $rowData['site'],
                            'description' => $rowData['description'],
                        ]);
                        $stats['imported']++;
                    }
                }
                break; // Only first sheet
            }

            DB::commit();
            $reader->close();

            $message = __('Import completed. Imported (New): :imported, Updated: :updated, Failed: :failed.', [
                'imported' => $stats['imported'],
                'updated' => $stats['updated'],
                'failed' => $stats['failed'],
            ]);
            if (!empty($stats['errors'])) {
                Log::warning('Location Import Errors', $stats['errors']);
                if (count($stats['errors']) > 0) {
                    $message .= " " . __('First error: :error', ['error' => $stats['errors'][0]]);
                }
            }

            return redirect()->route('locations.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $reader->close();
            Log::error('Location Import Failed', ['error' => $e->getMessage()]);
            return back()->with('error', __('Import failed: :message', ['message' => $e->getMessage()]));
        }
    }
}
