<?php

namespace App\Filament\Resources\ItemStocks;

use App\Models\Area;
use App\Enums\ItemType;
use App\Models\Location;
use App\Models\ItemStock;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ItemStocks\Pages\ManageItemStocks;

class ItemStockResource extends Resource
{
    protected static ?string $model = ItemStock::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->label('Nama Barang')
                    ->relationship(
                        name: 'item',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->where('type', ItemType::Consumable)
                    )
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->code} - {$record->name}")
                    ->searchable(['name', 'code'])
                    ->preload()
                    ->required()
                    ->unique(
                        table: 'item_stocks',
                        column: 'item_id',
                        modifyRuleUsing: function (Unique $rule, Get $get) {
                            return $rule->where('location_id', $get('location_id'));
                        },
                        ignoreRecord: true
                    )
                    ->validationMessages([
                        'unique' => 'Barang ini sudah terdaftar di lokasi yang dipilih.',
                    ])
                    ->columnSpanFull(),
                Select::make('location_id')
                    ->label('Lokasi Penyimpanan')
                    ->relationship(
                        name: 'location',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->with('area')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} - {$record->area->name}")
                    ->searchable(['name', 'code'])
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('quantity')
                    ->label('Qty. Stok')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
                TextInput::make('min_quantity')
                    ->label('Min. Stok')
                    ->helperText('Warna akan merah jika stok ≤ angka ini.')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required()
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['item', 'location.area']))
            ->heading('Stok Barang Habis Pakai')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('item.code')
                    ->label('Kode Aset')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),
                TextColumn::make('item.name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.area.name')
                    ->label('Area')
                    ->sortable()
                    ->badge()
                    ->color(
                        fn($record) => $record->location->area?->category?->getColor() ?? 'gray'
                    ),
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Qty. Stok')
                    ->sortable()
                    ->color(fn(ItemStock $record) => $record->quantity <= $record->min_quantity ? 'danger' : 'success')
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('min_quantity')
                    ->label('Min. Stok')
                    ->sortable()
                    ->color(fn(int $state): string => $state > 0 ? 'info' : 'gray')
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->size('sm'),
                IconColumn::make('deleted_at')
                    ->label('Status Data')
                    ->state(fn($record) => !is_null($record->deleted_at))
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-check-circle')
                    ->tooltip(fn(ItemStock $record) => $record->deleted_at ? 'Dihapus' : 'Aktif')
                    ->alignCenter(),
            ])->headerActions([
                    CreateAction::make()->label('Tambah Stok'),
                ])
            ->filters([
                SelectFilter::make('area')
                    ->label('Filter Area')
                    ->options(fn() => Area::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('location', fn($q) => $q->where('area_id', $data['value']));
                        }
                    })
                    ->preload()
                    ->searchable(),
                SelectFilter::make('location')
                    ->label('Filter Lokasi')
                    ->relationship('location', 'name')
                    ->preload()
                    ->multiple(),
                SelectFilter::make('item')
                    ->label('Filter Barang')
                    ->relationship(
                        name: 'item',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query
                            ->where('type', 'consumable')
                            ->orderBy('name')
                    )
                    ->preload()
                    ->multiple(),
                TrashedFilter::make()
                    ->label('Status Data')
                    ->placeholder('Hanya data aktif')
                    ->trueLabel('Tampilkan semua data')
                    ->falseLabel('Hanya data yang dihapus'),
                Filter::make('critical_stock')
                    ->label('Stok Menipis / Habis')
                    ->query(fn(Builder $query) => $query->whereColumn('quantity', '<=', 'min_quantity'))
                    ->toggle(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->action(function (ItemStock $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Data stok dihapus')->send();
                            } catch (ValidationException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Menghapus')
                                    ->body($e->validator->errors()->first())
                                    ->send();
                            }
                        }),

                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageItemStocks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['item', 'location'])
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
