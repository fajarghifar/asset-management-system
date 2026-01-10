<?php

namespace App\Filament\Resources\Loans\Traits;

use App\Models\Loan;
use App\Enums\LoanStatus;
use App\Enums\ProductType;
use Filament\Actions\Action;
use App\Services\LoanService;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

trait LoanActionsTrait
{
    /**
     * Define the header actions available for Loan View/Edit pages.
     * Contains logic for Approve, Reject, and Return Items.
     *
     * The Return Items action is a complex modal that allows partial or full returns.
     * It interacts with `LoanReturnService` to safely update inventory and asset statuses.
     *
     * @param Model|Loan $record
     * @return array
     */
    protected function getLoanHeaderActions(Model|Loan $record): array
    {
        $actions = [];

        // --- PENDING ACTION GROUP (Approve / Reject) ---
        if ($record->status === LoanStatus::Pending) {
            $actions[] = Action::make('approve')
                ->label(__('resources.loans.actions.approve'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('resources.loans.actions.approve_heading'))
                ->modalDescription(__('resources.loans.actions.approve_desc'))
                ->action(function () use ($record) {
                    try {
                        app(LoanService::class)->approveLoan($record);
                        Notification::make()->success()
                            ->title(__('resources.loans.notifications.approved_title'))
                            ->body(__('resources.loans.notifications.approved_body'))
                            ->send();
                        $this->redirect(request()->header('Referer'));
                    } catch (\Exception $e) {
                        Notification::make()->danger()
                            ->title(__('resources.loans.notifications.failed_title'))
                            ->body($e->getMessage())->send();
                    }
                });

            $actions[] = Action::make('reject')
                ->label(__('resources.loans.actions.reject'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('reason')
                        ->label(__('resources.loans.fields.reason'))
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) use ($record) {
                    try {
                        app(LoanService::class)->rejectLoan($record, $data['reason']);
                        Notification::make()->success()
                            ->title(__('resources.loans.notifications.rejected_title'))
                            ->body(__('resources.loans.notifications.rejected_body'))
                            ->send();
                        $this->redirect(request()->header('Referer'));
                    } catch (\Exception $e) {
                        Notification::make()->danger()->body($e->getMessage())->send();
                    }
                });

            $actions[] = DeleteAction::make();
        }

        // --- ACTIVE LOAN ACTIONS (Return Items) ---
        if (in_array($record->status, [LoanStatus::Approved, LoanStatus::Overdue])) {
            $actions[] = Action::make('returnItems')
                ->label(__('resources.loans.actions.return_items'))
                ->icon('heroicon-o-arrow-left-start-on-rectangle')
                ->color('info')
                ->modalWidth('4xl')
                // Pre-fill form with items that haven't been fully returned
                ->fillForm(function (Loan $record): array {
                    $itemsToReturn = $record->loanItems()
                        ->whereNull('returned_at')
                        ->with(['consumableStock.product', 'asset.product', 'asset'])
                        ->get()
                        ->map(function ($item) {
                            $productName = $item->product_name;
                            $identity = $item->type === ProductType::Asset ? $item->asset?->code : 'Stok Habis Pakai';

                            return [
                                'loan_item_id' => $item->id,
                                'type' => $item->type->value,
                                'item_name' => $productName,
                                'identity_info' => $identity,
                                'remaining_qty' => $item->quantity_borrowed - $item->quantity_returned,
                                'is_returning' => false,
                                'return_quantity' => 1,
                            ];
                        })->values()->toArray();

                    return ['items_to_return' => $itemsToReturn];
                })
                ->form([
                    Section::make()
                        ->schema([
                            Repeater::make('items_to_return')
                                ->label(__('resources.loans.fields.items_to_return'))
                                ->schema([
                                    Hidden::make('loan_item_id'),
                                    Hidden::make('type'),

                                    Grid::make(12)->schema([
                                        TextInput::make('item_name')
                                            ->label(__('resources.loans.fields.item_name'))
                                            ->disabled()
                                            ->columnSpan(4),

                                        TextInput::make('identity_info')
                                            ->label(__('resources.loans.fields.identity_info'))
                                            ->disabled()
                                            ->columnSpan(3),

                                        TextInput::make('remaining_qty')
                                            ->label(__('resources.loans.fields.remaining_qty'))
                                            ->disabled()
                                            ->integer()
                                            ->columnSpan(2),

                                        // Asset: Toggle for return (Default True as per requirement)
                                        Toggle::make('is_returning')
                                            ->label(__('resources.loans.fields.is_returning'))
                                            ->onColor('success')
                                            ->default(true)
                                            ->visible(fn($get) => $get('type') === ProductType::Asset->value)
                                            ->columnSpan(3)
                                            ->inline(false),

                                        // Consumable: Quantity Input for partial return
                                        TextInput::make('return_quantity')
                                            ->label(__('resources.loans.fields.return_input'))
                                            ->integer()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(fn($get) => (int) $get('remaining_qty'))
                                            ->visible(fn($get) => $get('type') === ProductType::Consumable->value)
                                            ->columnSpan(3),
                                    ]),
                                ])
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                        ])
                ])
                ->action(function (array $data) use ($record) {
                    try {
                        app(LoanService::class)->returnItems($record, $data['items_to_return'] ?? []);

                        Notification::make()->success()
                            ->title(__('resources.loans.notifications.returned_title'))
                            ->body(__('resources.loans.notifications.returned_body'))
                            ->send();
                        $this->redirect(request()->header('Referer'));
                    } catch (\Exception $e) {
                        Notification::make()->danger()
                            ->title(__('resources.loans.notifications.failed_title'))
                            ->body($e->getMessage())
                            ->send();
                    }
                });
        }

        return [
            ActionGroup::make($actions)
            ->hiddenLabel()
                ->color('primary')
                ->button(),
        ];
    }
}
