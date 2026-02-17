<?php

namespace App\Exports;

use App\Enums\LocationSite;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class LocationTemplateExport implements WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            __('Code'),
            __('Name'),
            __('Site (Select)'),
            __('Description'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // 1. Site Dropdown (Column C)
                $sites = collect(LocationSite::cases())
                    ->map(fn($site) => $site->value) // Use value (BT, JMP1) or Label? Model uses value cast, but maybe label is better for user?
                                                     // Actually Model casts to Enum, so we need Value to match Enum case backing string.
                                                     // But user might want to see "Batik Trusmi".
                                                     // Let's use "Value" for simplicity in parsing, or "Value - Label" and parse it.
                                                     // Simplest is Value (BT, JMP1) as they are short.
                    ->toArray();

                // Let's actually use "Value - Label" for better UX, and parse it in Controller.
                $sitesFormatted = collect(LocationSite::cases())
                     ->map(fn($site) => $site->value . ' - ' . $site->getLabel())
                     ->toArray();

                $this->addDropdown($event->sheet, 'C', $sitesFormatted, true);

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
                'C' => 'Z',
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
