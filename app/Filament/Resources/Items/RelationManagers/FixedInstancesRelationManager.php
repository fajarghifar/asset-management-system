<?php

namespace App\Filament\Resources\Items\RelationManagers;

use BackedEnum;
use App\Models\Area;
use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Location;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\FixedItemStatus;
use Filament\Actions\EditAction;
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
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\ValidationException;
use Filament\Resources\RelationManagers\RelationManager;

class FixedInstancesRelationManager extends RelationManager
{
    protected static string $relationship = 'fixedInstances';

    protected static ?string $title = 'Daftar Aset Tetap';

    protected static string|BackedEnum|null $icon = 'heroicon-o-tag';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Item && $ownerRecord->type === ItemType::Fixed;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Aset')
                    ->placeholder('Otomatis: [KODE_ITEM]-[TANGGAL]-[ACAK]')
                    ->disabled()
                    ->dehydrated()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->columnSpanFull(),
                TextInput::make('serial_number')
                    ->label('Nomor Seri')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->nullable()
                    ->placeholder('Opsional'),
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
                    ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} - {$record->area->name}")
                    ->searchable(['name', 'code'])
                    ->preload()
                    ->required(fn(Get $get) => $get('status') === FixedItemStatus::Available->value)
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Catatan Kondisi')
                    ->rows(3)
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
                    ->sortable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),
                TextColumn::make('serial_number')
                    ->label('Nomor Seri')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->placeholder('-'),
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
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
                    ->tooltip(fn(FixedItemInstance $record) => $record->deleted_at ? 'Dihapus' : 'Aktif')
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Unit'),
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
                    EditAction::make(),
                    DeleteAction::make()
                        ->action(function (Model $record) {
                            try {
                                $record->delete();
                                Notification::make()
                                    ->success()
                                    ->title('Aset berhasil dihapus')
                                    ->send();
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
            ]);
    }
}
