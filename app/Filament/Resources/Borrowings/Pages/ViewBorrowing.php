<?php

namespace App\Filament\Resources\Borrowings\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Borrowings\BorrowingResource;
use App\Filament\Resources\Borrowings\BorrowingActionsTrait;

class ViewBorrowing extends ViewRecord
{
    use BorrowingActionsTrait;

    protected static string $resource = BorrowingResource::class;

    protected function getHeaderActions(): array
    {
        $borrowingActions = $this->getBorrowingHeaderActions($this->getRecord());

        return [
            Action::make('back')
                ->label('Kembali')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-m-arrow-left'),
            ...$borrowingActions,
        ];
    }
}
