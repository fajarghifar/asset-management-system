<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Location;
use App\Enums\ProductType;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ConsumableStockTemplateExport implements WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            __('Product (Code | Name)'),
            __('Location (Code | Site - Name)'),
            __('Quantity'),
            __('Min Quantity'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // 1. Product Code Dropdown (Column A) - Type: Consumable
                $products = Product::where('type', ProductType::Consumable)
                    ->select(DB::raw("CONCAT(code, ' | ', name) as text"))
                    ->pluck('text')
                    ->toArray();
                $this->addDropdown($event->sheet, 'A', $products);

                // 2. Location Code Dropdown (Column B)
                $locations = Location::select(DB::raw("CONCAT(code, ' | ', site, ' - ', name) as text"))
                    ->pluck('text')
                    ->toArray();
                $this->addDropdown($event->sheet, 'B', $locations);

                // Auto-size columns
                foreach (range('A', 'D') as $col) {
                    $event->sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }

    private function addDropdown($sheet, $column, $options, $isSmallList = false)
    {
        if (empty($options)) return;

        $rowCount = count($options);
        $validation = $sheet->getCell("{$column}2")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input Error');
        $validation->setError('Value is not in list.');

        $formulaString = '"' . implode(',', $options) . '"';

        if ($isSmallList || ($rowCount < 50 && strlen($formulaString) < 255)) {
             $validation->setFormula1($formulaString);
        } else {
            // Use hidden column for large lists
            $hiddenCol = match($column) {
                'A' => 'Z',
                'B' => 'AA',
                default => 'AB'
            };

            $row = 1;
            foreach ($options as $option) {
                $sheet->setCellValue("{$hiddenCol}{$row}", $option);
                $row++;
            }
            $sheet->getColumnDimension($hiddenCol)->setVisible(false);
            $validation->setFormula1('$' . $hiddenCol . '$1:$' . $hiddenCol . '$' . $rowCount);
        }

        // Apply to rows 2-1000
        for ($i = 2; $i <= 1000; $i++) {
            $sheet->getCell("{$column}{$i}")->setDataValidation(clone $validation);
        }
    }
}
