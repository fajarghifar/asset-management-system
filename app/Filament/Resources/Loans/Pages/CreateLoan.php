<?php

namespace App\Filament\Resources\Loans\Pages;

use Filament\Actions\Action;
use App\Services\LoanService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Loans\LoanResource;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('resources.general.actions.back'))
                ->icon('heroicon-m-arrow-left')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    /**
     * Handle the record creation manually.
     *
     * We override this method to have full control over the Loan creation process,
     * specifically to handle the "Loan Items" persistence. Since the LoanForm
     * disables `relationship()` binding on the Create page (to avoid polymorphic complexity),
     * we receive the `loanItems` as a raw array in `$data`.
     *
     * @param array $data
     * @return Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['loanItems'] ?? [];
        unset($data['loanItems']);

        // Use LoanService to handle creation, code generation, and item persistence transactionally
        return app(LoanService::class)->createLoan($data, $items);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }
}
