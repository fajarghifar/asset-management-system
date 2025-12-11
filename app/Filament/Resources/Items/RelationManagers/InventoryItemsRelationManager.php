<?php

namespace App\Filament\Resources\Items\RelationManagers;

use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Location;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\InventoryItem;
use App\Enums\InventoryStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Actions\ForceDeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class InventoryItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryItems';

    protected static ?string $title = 'Daftar Barang Inventaris';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Item &&
            in_array($ownerRecord->type, [ItemType::Consumable->value, ItemType::Fixed->value]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TextInput::make('code')
                //     ->label('Kode Aset')
                //     ->placeholder('Otomatis: [KODE_ITEM]-[TANGGAL]-[ACAK]')
                //     ->disabled()
                //     ->dehydrated()
                //     ->unique(ignoreRecord: true)
                //     ->maxLength(50)
                //     ->columnSpanFull(),
                Select::make('location_id')
                    ->label('Lokasi Pemasangan')
                    ->relationship(
                        name: 'location',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->with('area')
                    )
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} - {$record->area->name}")
                    ->searchable(['name', 'code'])
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('serial_number')
                    ->label('Nomor Seri')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                // --- UI KHUSUS FIXED ASSET ---
                Select::make('status')
                    ->label('Status')
                    ->options(InventoryStatus::class)
                    ->default(InventoryStatus::Available)
                    ->required()
                    ->visible(fn(Get $get) => !$get('is_consumable')),
                // --- UI KHUSUS CONSUMABLE ---
                TextInput::make('quantity')
                    ->label('Qty. Stok')
                    ->numeric()
                    ->default(1)
                    ->minValue(0)
                    ->required()
                    ->disabled(fn(Get $get) => !$get('is_consumable'))
                    ->visible(fn(Get $get) => $get('is_consumable')),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['location.area']))
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('code')
                    ->label('Kode Aset')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),
                TextColumn::make('serial_number')
                    ->label('Nomor Seri')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-')
                    ->fontFamily('mono'),
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.area.name')
                    ->label('Area')
                    ->sortable()
                    ->badge()
                    ->color(fn(InventoryItem $record): ?string => $record->location?->area?->category?->getColor() ?? 'gray'),
                TextColumn::make('quantity')
                    ->label('Qty. Stok')
                    ->sortable()
                    ->color(fn(InventoryItem $record) => $record->quantity <= $record->min_quantity ? 'danger' : 'success')
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('deleted_at')
                    ->label('Status Data')
                    ->state(fn($record) => !is_null($record->deleted_at))
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-check-circle')
                    ->tooltip(fn(InventoryItem $record) => $record->deleted_at ? 'Dihapus' : 'Aktif')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(InventoryStatus::class),
                SelectFilter::make('area')
                    ->label('Area')
                    ->relationship('location.area', 'name'),
                TrashedFilter::make()
                    ->label('Status Data')
                    ->placeholder('Hanya data aktif')
                    ->trueLabel('Tampilkan semua data')
                    ->falseLabel('Hanya data yang dihapus'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Barang'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->action(function (InventoryItem $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Data berhasil dihapus')->send();
                            } catch (ValidationException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Menghapus')
                                    ->body($e->validator->errors()->first())
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Terjadi Kesalahan')
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                //
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
