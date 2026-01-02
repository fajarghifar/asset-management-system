<?php

namespace App\Filament\Resources\Loans\Pages;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Loans\LoanResource;
use App\Filament\Resources\Loans\Traits\LoanActionsTrait;

class EditLoan extends EditRecord
{
    use LoanActionsTrait;

    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return array_merge(
            [
                Action::make('back')
                    ->label(__('resources.general.actions.back'))
                    ->url($this->getResource()::getUrl('index'))
                    ->color('gray')
                    ->icon('heroicon-m-arrow-left'),
                ViewAction::make(),
            ],
            $this->getLoanHeaderActions($this->getRecord())
        );
    }
}
