<?php

namespace App\Livewire\Loans;

use App\Models\Loan;
use App\Enums\LoanStatus;
use App\Services\LoanService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;

final class LoanTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'loan-table';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('loans_export_' . now()->format('Y_m_d'))
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Loan::query()->with(['user']);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('code')
            ->add('borrower_display', fn ($loan) => $loan->user ? $loan->user->name : $loan->borrower_name)
            ->add('loan_date_formatted', fn ($loan) => $loan->loan_date->format('d/m/Y'))
            ->add('due_date_formatted', fn ($loan) => $loan->due_date->format('d/m/Y'))
            ->add('returned_date_formatted', fn ($loan) => $loan->returned_date ? $loan->returned_date->format('d/m/Y') : '-')
            ->add('status_label', function ($loan) {
                $status = $loan->status;
                $color = $status->getColor();
                $label = $status->getLabel();

                $colorClasses = match ($color) {
                    'success' => 'bg-green-100 text-green-800 border-green-200',
                    'warning' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'danger' => 'bg-red-100 text-red-800 border-red-200',
                    'secondary', 'gray' => 'bg-gray-100 text-gray-800 border-gray-200',
                    default => 'bg-gray-100 text-gray-800 border-gray-200',
                };

                return Blade::render(
                    '<div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $colorClasses . '">
                        ' . $label . '
                    </div>'
                );
            });
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')->hidden(),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Borrower', 'borrower_display', 'borrower_name')
                ->searchable(),

            Column::make('Loan Date', 'loan_date_formatted', 'loan_date')
                ->sortable(),

            Column::make('Due Date', 'due_date_formatted', 'due_date')
                ->sortable(),

            Column::make('Returned', 'returned_date_formatted', 'returned_date')
                ->sortable(),

            Column::make(__('Status'), 'status_label', 'status')
                ->sortable(),

            Column::action(__('Action'))
        ];
    }

    public function filters(): array
    {
        return [
            Filter::multiSelect('status_label', 'status')
                ->dataSource(collect(LoanStatus::cases())->map(fn($s) => [
                    'label' => $s->getLabel(),
                    'value' => $s->value,
                ])->toArray())
                ->optionLabel('label')
                ->optionValue('value'),


            Filter::datepicker('loan_date_formatted', 'loan_date')
                ->params(['enableTime' => false]),
        ];
    }

    public function actions(Loan $row): array
    {
        $borrowerName = $row->user ? $row->user->name : $row->borrower_name;

        $actions = [
            Button::add('view')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>')
                ->class('bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-md flex items-center justify-center mr-1')
                ->route('loans.show', ['loan' => $row->id])
                ->tooltip(__('View Details')),
        ];

        if ($row->status === LoanStatus::Pending) {
            $actions[] = Button::add('edit')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>')
                ->class('bg-amber-500 hover:bg-amber-600 text-white p-2 rounded-md flex items-center justify-center mr-1')
                ->route('loans.edit', ['loan' => $row->id])
                ->tooltip(__('Edit Loan'));

            $actions[] = Button::add('approve')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>')
                ->class('bg-green-500 hover:bg-green-600 text-white p-2 rounded-md flex items-center justify-center mr-1')
                ->dispatch('open-delete-modal', [
                    'component' => 'loans.loan-table',
                    'method' => 'approve-loan',
                    'params' => ['rowId' => $row->id],
                    'title' => __('Approve Loan?'),
                    'description' => __("Are you sure you want to approve Loan #:code for :name? Stock will be deducted.", ['code' => $row->code, 'name' => $borrowerName]),
                    'confirmButtonText' => __('Approve'),
                    'confirmButtonClass' => 'bg-green-600 hover:bg-green-700 text-white',
                ])
                ->tooltip(__('Approve Loan'));

            $actions[] = Button::add('reject')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>')
                ->class('bg-red-500 hover:bg-red-600 text-white p-2 rounded-md flex items-center justify-center mr-1')
                ->dispatch('open-delete-modal', [
                    'component' => 'loans.loan-table',
                    'method' => 'reject-loan',
                    'params' => ['rowId' => $row->id],
                    'title' => __('Reject Loan?'),
                    'description' => __("Are you sure you want to reject Loan #:code for :name?", ['code' => $row->code, 'name' => $borrowerName]),
                    'confirmButtonText' => __('Reject'),
                    'confirmButtonClass' => 'bg-red-600 hover:bg-red-700 text-white',
                ])
                ->tooltip(__('Reject Loan'));

            $actions[] = Button::add('delete')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>')
                ->class('bg-red-500 hover:bg-red-600 text-white p-2 rounded-md flex items-center justify-center')
                ->dispatch('open-delete-modal', [
                    'component' => 'loans.loan-table',
                    'method' => 'delete-loan',
                    'params' => ['rowId' => $row->id],
                    'title' => __('Delete Loan?'),
                    'description' => __("Are you sure you want to delete Loan #:code for :name? This action cannot be undone.", ['code' => $row->code, 'name' => $borrowerName]),
                    'confirmButtonText' => __('Delete'),
                    'confirmButtonClass' => 'bg-red-600 hover:bg-red-700 text-white',
                ])
                ->tooltip(__('Delete Loan'));
        } elseif ($row->status === LoanStatus::Rejected) {
            $actions[] = Button::add('restore')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>')
                ->class('bg-amber-500 hover:bg-amber-600 text-white p-2 rounded-md flex items-center justify-center mr-1')
                ->dispatch('open-delete-modal', [
                    'component' => 'loans.loan-table',
                    'method' => 'restore-loan',
                    'params' => ['rowId' => $row->id],
                    'title' => __('Restore Loan?'),
                    'description' => __("Are you sure you want to restore Loan #:code for :name to Pending?", ['code' => $row->code, 'name' => $borrowerName]),
                    'confirmButtonText' => __('Restore'),
                    'confirmButtonClass' => 'bg-amber-600 hover:bg-amber-700 text-white',
                ])
                ->tooltip(__('Restore Loan'));

            $actions[] = Button::add('delete')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>')
                ->class('bg-red-500 hover:bg-red-600 text-white p-2 rounded-md flex items-center justify-center')
                ->dispatch('open-delete-modal', [
                    'component' => 'loans.loan-table',
                    'method' => 'delete-loan',
                    'params' => ['rowId' => $row->id],
                    'title' => __('Delete Loan?'),
                    'description' => __("Are you sure you want to delete Loan #:code for :name? This action cannot be undone.", ['code' => $row->code, 'name' => $borrowerName]),
                    'confirmButtonText' => __('Delete'),
                    'confirmButtonClass' => 'bg-red-600 hover:bg-red-700 text-white',
                ])
                ->tooltip(__('Delete Loan'));
        }

        return $actions;
    }

    #[\Livewire\Attributes\On('approve-loan')]
    public function approve($rowId, LoanService $service): void
    {
        $loan = Loan::find($rowId);
        if ($loan) {
            try {
                $service->approveLoan($loan);
                $this->dispatch('pg:eventRefresh-loan-table');
                $this->dispatch('toast', message: "Loan approved successfully.", type: 'success');
            } catch (\Exception $e) {
                $this->dispatch('toast', message: 'Failed to approve loan: ' . $e->getMessage(), type: 'error');
            }
        }
    }

    #[\Livewire\Attributes\On('reject-loan')]
    public function reject($rowId, LoanService $service): void
    {
        $loan = Loan::find($rowId);
        if ($loan) {
            try {
                $service->rejectLoan($loan);
                $this->dispatch('pg:eventRefresh-loan-table');
                $this->dispatch('toast', message: "Loan rejected.", type: 'success');
            } catch (\Exception $e) {
                $this->dispatch('toast', message: 'Failed to reject loan: ' . $e->getMessage(), type: 'error');
            }
        }
    }

    #[\Livewire\Attributes\On('restore-loan')]
    public function restore($rowId, LoanService $service): void
    {
        $loan = Loan::find($rowId);
        if ($loan) {
            try {
                $service->restoreLoan($loan);
                $this->dispatch('pg:eventRefresh-loan-table');
                $this->dispatch('toast', message: "Loan restored to pending.", type: 'success');
            } catch (\Exception $e) {
                $this->dispatch('toast', message: 'Failed to restore loan: ' . $e->getMessage(), type: 'error');
            }
        }
    }

    #[\Livewire\Attributes\On('delete-loan')]
    public function delete($rowId, LoanService $service): void
    {
        $loan = Loan::find($rowId);
        if ($loan) {
            try {
                $service->deleteLoan($loan);
                $this->dispatch('pg:eventRefresh-loan-table');
                $this->dispatch('toast', message: "Loan deleted successfully.", type: 'success');
            } catch (\Exception $e) {
                $this->dispatch('toast', message: 'Failed to delete loan: ' . $e->getMessage(), type: 'error');
            }
        }
    }
}
