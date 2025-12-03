<?php

namespace App\Filament\Resources\Items\RelationManagers;

use BackedEnum;
use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Location;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use App\Models\InstalledItemInstance;
use Filament\Forms\Components\Select;
use Filament\Actions\ForceDeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Validation\ValidationException;
use Filament\Resources\RelationManagers\RelationManager;

class InstalledInstancesRelationManager extends RelationManager
{
    protected static string $relationship = 'installedInstances';

    protected static ?string $title = 'Unit Terpasang';

    protected static string|BackedEnum|null $icon = 'heroicon-o-wrench-screwdriver';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Item && $ownerRecord->type === ItemType::Installed;
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
                    ->unique(ignoreRecord: true),
                Select::make('current_location_id')
                    ->label('Lokasi Pemasangan')
                    ->relationship(
                        name: 'currentLocation',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->with('area')
                    )
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} - {$record->area->name}")
                    ->searchable(['name', 'code'])
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('installed_at')
                    ->label('Tanggal Pemasangan')
                    ->required()
                    ->maxDate(now()),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['currentLocation.area']))
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
                    ->placeholder('-')
                    ->fontFamily('mono'),
                TextColumn::make('currentLocation.area.name')
                    ->label('Area')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(
                        fn(InstalledItemInstance $record) => $record->currentLocation->area?->category?->getColor() ?? 'gray'
                    ),
                TextColumn::make('currentLocation.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('installed_at')
                    ->label('Tgl. Pemasangan')
                    ->date('d M Y')
                    ->sortable(),
                IconColumn::make('deleted_at')
                    ->label('Status Data')
                    ->state(fn($record) => !is_null($record->deleted_at))
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-check-circle')
                    ->tooltip(fn(InstalledItemInstance $record) => $record->deleted_at ? 'Dihapus' : 'Aktif')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('area')
                    ->label('Area')
                    ->relationship('currentLocation.area', 'name'),
                TrashedFilter::make()
                    ->label('Status Data')
                    ->placeholder('Hanya data aktif')
                    ->trueLabel('Tampilkan semua data')
                    ->falseLabel('Hanya data yang dihapus'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Unit'),
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
