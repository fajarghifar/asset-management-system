<?php

namespace App\Filament\Resources\FixedItemInstances;

use App\Models\Area;
use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Location;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\FixedItemStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use App\Models\FixedItemInstance;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
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
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FixedItemInstances\Pages\ManageFixedItemInstances;

class FixedItemInstanceResource extends Resource
{
    protected static ?string $model = FixedItemInstance::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->label('Nama Barang (Master)')
                    ->relationship(
                        name: 'item',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->where('type', ItemType::Fixed)
                    )
                    ->getOptionLabelFromRecordUsing(fn(Item $record) => "{$record->code} - {$record->name}")
                    ->searchable(['name', 'code'])
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('code')
                    ->label('Kode Aset')
                    ->placeholder('Otomatis: [KODE_ITEM]-[TANGGAL]-[ACAK]')
                    ->disabled()
                    ->dehydrated()
                    ->unique(ignoreRecord: true)
                    ->maxLength(30),
                TextInput::make('serial_number')
                    ->label('Nomor Seri (SN)')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->nullable()
                    ->placeholder('Opsional (SN Pabrik)'),
                Select::make('status')
                    ->label('Status Kondisi')
                    ->options(FixedItemStatus::class)
                    ->default(FixedItemStatus::Available)
                    ->required()
                    ->live(),
                Select::make('location_id')
                    ->label('Lokasi Penyimpanan')
                    ->relationship(
                        name: 'location',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->with('area')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} ({$record->area->name})")
                    ->searchable(['name', 'code'])
                    ->preload()
                    ->required(fn(Get $get) => $get('status') === FixedItemStatus::Available->value)
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Aset Tetap')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('code')
                    ->label('Kode Aset')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),
                TextColumn::make('item.name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->label('Nomor Seri')
                    ->searchable()
                    ->copyable(),
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('deleted_at')
                    ->label('Status Data')
                    ->state(fn($record) => !is_null($record->deleted_at))
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-check-circle')
                    ->tooltip(fn(FixedItemInstance $record) => $record->deleted_at ? 'Dihapus' : 'Aktif')
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Barang'),
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
                    ->searchable()
                    ->preload()
                    ->optionsLimit(5),
                SelectFilter::make('location')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload()
                    ->optionsLimit(5),
                SelectFilter::make('item_id')
                    ->label('Nama Barang')
                    ->options(fn() => Item::where('type', ItemType::Fixed)->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->optionsLimit(5),
                SelectFilter::make('status')
                    ->options(FixedItemStatus::class),
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
                        ->action(function (FixedItemInstance $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Aset berhasil dihapus')->send();
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
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFixedItemInstances::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['item', 'location.area'])
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
