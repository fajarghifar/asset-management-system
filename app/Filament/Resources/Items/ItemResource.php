<?php

namespace App\Filament\Resources\Items;

use App\Models\Item;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use App\Services\ItemDeletionService;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use App\Services\ItemManagementService;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\Items\Pages\ManageItems;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Jenis Barang')
                    ->options([
                        'fixed' => 'Barang Tetap',
                        'consumable' => 'Barang Habis Pakai',
                        'installed' => 'Barang Terpasang',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('code')
                    ->label('Kode Barang')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->autofocus(),
                TextInput::make('name')
                    ->label('Nama Barang')
                    ->required()
                    ->maxLength(100),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Barang')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('No.')
                    ->rowIndex()
                    ->width('30px'),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'fixed' => 'success',
                        'consumable' => 'warning',
                        'installed' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'fixed' => 'Barang Tetap',
                        'consumable' => 'Habis Pakai',
                        'installed' => 'Terpasang',
                        default => $state,
                    }),
                // Kolom instance untuk fixed/installed
                TextColumn::make('fixed_instances_count')
                    ->label('Instance (Tetap)')
                    ->toggleable()
                    ->formatStateUsing(fn($state) => $state > 0 ? "{$state} unit" : '–'),
                TextColumn::make('installed_instances_count')
                    ->label('Instance (Terpasang)')
                    ->toggleable()
                    ->formatStateUsing(fn($state) => $state > 0 ? "{$state} unit" : '–'),
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
                    ->options([
                        'fixed' => 'Barang Tetap',
                        'consumable' => 'Barang Habis Pakai',
                        'installed' => 'Barang Terpasang',
                    ]),
                TrashedFilter::make()
                    ->label('Status Data')
                    ->placeholder('Hanya data aktif')
                    ->trueLabel('Tampilkan semua data')
                    ->falseLabel('Hanya data yang dihapus'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->iconSize('lg'),
                    EditAction::make()
                        ->iconSize('lg')
                        ->mutateDataUsing(function (array $data, Item $record) {
                            if ($record->type !== $data['type']) {
                                try {
                                    (new ItemManagementService())->validateTypeChange($record, $data['type']);
                                } catch (ValidationException $e) {
                                    Notification::make()
                                        ->title('Gagal Mengubah Tipe')
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                    return [];
                                }
                            }
                            return $data;
                        }),
                    DeleteAction::make()
                        ->iconSize('lg')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Barang?')
                        ->modalDescription('Barang dan semua datanya akan disembunyikan.')
                        ->action(function (Item $record) {
                            try {
                                (new ItemManagementService())->delete($record);
                                Notification::make()
                                    ->title('Berhasil')
                                    ->body("{$record->name} berhasil dihapus.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal Menghapus')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    ForceDeleteAction::make()->iconSize('lg'),
                    RestoreAction::make()->iconSize('lg'),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $deleted = 0;
                            $errors = [];
                            foreach ($records as $record) {
                                try {
                                    (new ItemManagementService())->delete($record);
                                    $deleted++;
                                } catch (\Exception $e) {
                                    $errors[] = "{$record->name}: " . $e->getMessage();
                                }
                            }
                            if ($deleted > 0) {
                                Notification::make()
                                    ->title("Berhasil menghapus {$deleted} barang")
                                    ->success()
                                    ->send();
                            }
                            if (!empty($errors)) {
                                Notification::make()
                                    ->title('Beberapa barang gagal dihapus')
                                    ->body(implode('\n', $errors))
                                    ->danger()
                                    ->send();
                            }
                        }),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageItems::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['fixedInstances', 'installedInstances'])
            ->withTrashed();
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
