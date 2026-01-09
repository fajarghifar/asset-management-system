<?php

namespace App\Filament\Resources\Loans\Traits;

use App\Models\Loan;
use Filament\Actions\Action;
use App\Enums\LoanStatus;
use App\Enums\ProductType;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Services\LoanReturnService;
use App\Services\LoanApprovalService;

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
                        app(LoanApprovalService::class)->approve($record);
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
                        app(LoanApprovalService::class)->reject($record, $data['reason']);
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
                        ->whereRaw('quantity_borrowed > quantity_returned')
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
                    $service = app(LoanReturnService::class);
                    $processed = 0;

                    foreach ($data['items_to_return'] ?? [] as $returnData) {
                        $loanItem = $record->loanItems->find($returnData['loan_item_id']);
                        if (!$loanItem) continue;

                        $stockIncrement = 0;
                        $resolutionIncrement = 0;

                        if ($returnData['type'] === ProductType::Asset->value) {
                            if (!empty($returnData['is_returning'])) {
                                $stockIncrement = 1;
                                $resolutionIncrement = 1;
                                $isResolved = true;
                            }
                        } else {
                            $stockIncrement = (int) ($returnData['return_quantity'] ?? 0);
                            $resolutionIncrement = $stockIncrement;
                            $isResolved = true;
                        }

                        if ($isResolved || $resolutionIncrement > 0) {
                            $service->processReturn($record, $loanItem, $stockIncrement, $resolutionIncrement, $isResolved);
                            $processed++;
                        }
                    }

                    if ($processed > 0) {
                        Notification::make()->success()
                            ->title(__('resources.loans.notifications.returned_title'))
                            ->body(__('resources.loans.notifications.returned_body'))
                            ->send();
                        $this->redirect(request()->header('Referer'));
                    } else {
                        Notification::make()->warning()
                            ->title(__('resources.loans.notifications.return_canceled_title'))
                            ->body(__('resources.loans.notifications.return_canceled_body'))
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
