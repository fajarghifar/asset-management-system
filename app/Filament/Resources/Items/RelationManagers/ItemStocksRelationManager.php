<?php

namespace App\Filament\Resources\Items\RelationManagers;

use App\Models\ItemStock;
use BackedEnum;
use App\Models\Area;
use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Location;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Filament\Resources\RelationManagers\RelationManager;

class ItemStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'stocks';

    protected static ?string $title = 'Stok per Lokasi';

    protected static string|BackedEnum|null $icon = 'heroicon-o-cube';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Item && $ownerRecord->type === ItemType::Consumable;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->optionsLimit(10)
                    ->required()
                    ->unique(
                        table: 'item_stocks',
                        column: 'location_id',
                        modifyRuleUsing: function (Unique $rule, RelationManager $livewire) {
                            return $rule->where('item_id', $livewire->getOwnerRecord()->id);
                        },
                        ignoreRecord: true
                    )
                    ->validationMessages([
                        'unique' => 'Barang ini sudah terdaftar di lokasi tersebut.',
                    ])
                    ->columnSpanFull(),
                Grid::make(2)->schema([
                    TextInput::make('quantity')
                        ->label('Qty. Stok')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required()
                        ->columnSpan(1),
                    TextInput::make('min_quantity')
                        ->label('Min. Stok')
                        ->helperText('Warna akan merah jika stok ≤ angka ini.')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required()
                        ->columnSpan(1),
                ])
                ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['location.area']))
            ->heading('Distribusi Stok')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('location.area.name')
                    ->label('Area')
                    ->sortable()
                    ->badge()
                    ->color(
                        fn($record) => $record->location->area?->category?->getColor() ?? 'gray'
                    ),
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Qty. Stok')
                    ->sortable()
                    ->color(fn(Model $record) => $record->quantity <= $record->min_quantity ? 'danger' : 'success')
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
                    ->sortable(),
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
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Lokasi Stok'),
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
                Filter::make('critical_stock')
                    ->label('Stok Menipis / Habis')
                    ->query(fn(Builder $query) => $query->whereColumn('quantity', '<=', 'min_quantity'))
                    ->toggle()
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->action(function (Model $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Stok lokasi dihapus')->send();
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
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                //
            ])
            ->defaultSort('quantity', 'asc');
    }
}
