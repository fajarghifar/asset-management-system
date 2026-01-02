<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Enums\LoanStatus;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Loans\LoanResource;
use App\Filament\Resources\Loans\Traits\LoanActionsTrait;

class ViewLoan extends ViewRecord
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
                EditAction::make()->visible(fn($record) => $record->status === LoanStatus::Pending),
            ],
            $this->getLoanHeaderActions($this->getRecord())
        );
    }
}
