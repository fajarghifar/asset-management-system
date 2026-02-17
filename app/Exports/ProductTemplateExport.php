<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ProductTemplateExport implements WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            __('Code'),
            __('Name'),
            __('Type (Select)'),
            __('Category (Select)'),
            __('Loanable (Select)'),
            __('Description'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // 1. Product Type Dropdown (Column C)
                $this->addDropdown($event->sheet, 'C', ['Asset', 'Consumable'], true);

                // 2. Category Dropdown (Column D)
                $categories = Category::pluck('name')->toArray();
                $this->addDropdown($event->sheet, 'D', $categories);

                // 3. Loanable Dropdown (Column E)
                $this->addDropdown($event->sheet, 'E', ['Yes', 'No'], true);

                // Auto-size columns
                foreach (range('A', 'F') as $col) {
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
                'C' => 'Z',
                'D' => 'AA',
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

    }
}
