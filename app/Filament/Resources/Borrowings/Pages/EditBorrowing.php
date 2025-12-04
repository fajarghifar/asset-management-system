<?php

namespace App\Filament\Resources\Borrowings\Pages;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Borrowings\BorrowingResource;
use App\Filament\Resources\Borrowings\BorrowingActionsTrait;

class EditBorrowing extends EditRecord
{
    use BorrowingActionsTrait;

    protected static string $resource = BorrowingResource::class;

    protected function getHeaderActions(): array
    {
        return array_merge(
            [
                Action::make('back')
                    ->label('Kembali')
                    ->url($this->getResource()::getUrl('index'))
                    ->color('gray')
                    ->icon('heroicon-m-arrow-left'),
                ViewAction::make(),
            ],
            $this->getBorrowingHeaderActions($this->getRecord())
        );
    }
}
