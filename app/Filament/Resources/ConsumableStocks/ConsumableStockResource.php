<?php

namespace App\Filament\Resources\ConsumableStocks;

use UnitEnum;
use App\Models\Location;
use Filament\Tables\Table;
use App\Enums\LocationSite;
use App\Enums\ProductType;
use Filament\Schemas\Schema;
use App\Models\ConsumableStock;
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\QueryException;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ConsumableStocks\Pages\ManageConsumableStocks;

class ConsumableStockResource extends Resource
{
    protected static ?string $model = ConsumableStock::class;
    protected static string|UnitEnum|null $navigationGroup = 'Inventaris';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Stok Consumable';
    protected static ?string $pluralModelLabel = 'Stok Consumable';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Barang')
                    ->relationship('product', 'name', fn($query) => $query->where('type', ProductType::Consumable))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                Select::make('location_id')
                    ->label('Lokasi')
                    ->relationship('location', 'name')
                    ->getOptionLabelFromRecordUsing(fn(Location $record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('quantity')
                    ->label('Jumlah Stok Saat Ini')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),

                TextInput::make('min_quantity')
                    ->label('Batas Minimum Stok (Alert)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Stok Barang Habis Pakai')
            ->defaultSort('quantity', 'asc')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('product.name')
                    ->label('Barang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location.site')
                    ->label('Gedung / Site')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Sisa Stok Aktual')
                    ->badge()
                    ->color(fn(ConsumableStock $record) => $record->quantity <= $record->min_quantity ? 'danger' : 'success')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('min_quantity')
                    ->label('Batas Min.')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Stok Baru'),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->label('Barang')
                    ->relationship('product', 'name', fn ($query) => $query->where('type', ProductType::Consumable))
                    ->searchable()
                    ->preload(),
                Filter::make('filter_location')
                    ->form([
                        Select::make('site')
                            ->label('Site / Gedung')
                            ->options(LocationSite::class)
                            ->searchable()
                            ->multiple()
                            ->native(false)
                            ->live(),
                        Select::make('location_id')
                            ->label('Area / Ruangan')
                            ->searchable()
                            ->multiple()
                            ->native(false)
                            ->options(fn ($get) =>
                                Location::query()
                                    ->when(
                                        !empty($get('site')),
                                        fn ($q) => $q->whereIn('site', $get('site'))
                                    )
                                    ->pluck('name', 'id')
                            ),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                !empty($data['site']),
                                fn ($q) => $q->whereHas('location', fn ($l) => $l->whereIn('site', $data['site']))
                            )
                            ->when(
                                !empty($data['location_id']),
                                fn ($q) => $q->whereIn('location_id', $data['location_id'])
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->modalDescription('Apakah Anda yakin ingin menghapus data stok ini? Tindakan ini tidak dapat dibatalkan.')
                        ->action(function (ConsumableStock $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Data stok berhasil dihapus')->send();
                            } catch (QueryException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Penghapusan Gagal')
                                    ->body('Data stok ini tidak bisa dihapus karena sedang digunakan oleh data lain.')
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageConsumableStocks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'location']);
    }
}
