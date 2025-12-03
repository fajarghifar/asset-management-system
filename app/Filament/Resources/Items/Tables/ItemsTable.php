<?php

namespace App\Filament\Resources\Items\Tables;

use App\Models\Item;
use App\Enums\ItemType;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Validation\ValidationException;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->withCount(['fixedInstances', 'installedInstances'])
                    ->withSum('stocks', 'quantity');
            })
            ->heading('Daftar Master Barang')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Total Stok / Unit')
                    ->state(function (Item $record) {
                        return match ($record->type) {
                            ItemType::Consumable => $record->stocks_sum_quantity ?? 0,
                            ItemType::Fixed => $record->fixed_instances_count ?? 0,
                            ItemType::Installed => $record->installed_instances_count ?? 0,
                        };
                    })
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                    ->alignCenter(),
                IconColumn::make('deleted_at')
                    ->label('Status')
                    ->state(fn($record) => !is_null($record->deleted_at))
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-check-circle')
                    ->tooltip(fn(Item $record) => $record->deleted_at ? 'Dihapus' : 'Aktif'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Barang'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(ItemType::class),
                TrashedFilter::make()
                    ->label('Status Data')
                    ->placeholder('Hanya data aktif')
                    ->trueLabel('Tampilkan semua data')
                    ->falseLabel('Hanya data yang dihapus'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()

                        ->modalHeading('Hapus Barang')
                        ->modalDescription('Apakah Anda yakin? Barang akan dipindahkan ke sampah (Trash).')
                        ->action(function (Item $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Barang berhasil dihapus')->send();
                            } catch (ValidationException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Menghapus')
                                    ->body($e->validator->errors()->first())
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Error Sistem')
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),

                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])
                ->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                //
            ])
            ->striped();
    }
}
