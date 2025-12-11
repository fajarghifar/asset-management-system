<?php

namespace App\Filament\Resources\InventoryItems;

use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Location;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\InventoryItem;
use App\Enums\InventoryStatus;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Actions\ForceDeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InventoryItems\Pages\ManageInventoryItems;

class InventoryItemResource extends Resource
{
    protected static ?string $model = InventoryItem::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->label('Nama Barang')
                    ->relationship(
                        'item',
                        'name',
                        fn($query) => $query->whereIn('type', [ItemType::Fixed, ItemType::Consumable])
                    )
                    ->getOptionLabelFromRecordUsing(fn(Item $record) => "{$record->name} ({$record->code})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            $item = Item::find($state);
                            $set('is_consumable', $item?->type === ItemType::Consumable);

                            if ($item?->type === ItemType::Fixed) {
                                $set('quantity', 1);
                            }
                        }
                    })
                    ->columnSpanFull(),
                TextInput::make('serial_number')
                    ->label('Nomor Seri')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->nullable(),
                Select::make('location_id')
                    ->label('Lokasi Penyimpanan')
                    ->relationship('location', 'name')
                    ->searchable(['name'])
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} - {$record->area->name}")
                    ->required(),

                // --- UI KHUSUS FIXED ASSET (Hidden jika Consumable) ---
                Select::make('status')
                    ->label('Status')
                    ->options(InventoryStatus::class)
                    ->default(InventoryStatus::Available)
                    ->required()
                    ->visible(fn (Get $get) => !$get('is_consumable')),

                // --- UI KHUSUS CONSUMABLE (Hidden jika Fixed) ---
                TextInput::make('quantity')
                    ->label('Qty. Stok')
                    ->numeric()
                    ->default(1)
                    ->minValue(0)
                    ->required()
                    ->disabled(fn(Get $get) => !$get('is_consumable'))
                    ->visible(fn(Get $get) => $get('is_consumable')),
                TextInput::make('min_quantity')
                    ->label('Min. Stok')
                    ->numeric()
                    ->default(5)
                    ->visible(fn(Get $get) => $get('is_consumable')),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),

                // Hidden field bantu untuk logika UI (reaktif terhadap tipe item)
                Hidden::make('is_consumable')
                    ->default(false)
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                TextColumn::make('item.name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item.type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->label('Nomor Seri')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->placeholder('-'),
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
            ->headerActions([
                CreateAction::make()->label('Tambah Barang'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(InventoryStatus::class),
                SelectFilter::make('item_id')
                    ->label('Nama Barang')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('area')
                    ->label('Area')
                    ->relationship('location.area', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TrashedFilter::make()
                    ->label('Status Data')
                    ->placeholder('Hanya data aktif')
                    ->trueLabel('Tampilkan semua data')
                    ->falseLabel('Hanya data yang dihapus'),
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
                ]),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInventoryItems::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
