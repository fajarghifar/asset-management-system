<?php

namespace App\Filament\Resources\Items\RelationManagers;

use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Location;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\InstalledItem;
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
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Validation\ValidationException;
use Filament\Resources\RelationManagers\RelationManager;

class InstalledItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'installedItems';

    protected static ?string $title = 'Daftar Barang Terpasang';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Item && $ownerRecord->type === ItemType::Installed;
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
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('installed_at')
                    ->label('Tanggal Pemasangan')
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
                    ->tooltip(fn(InstalledItem $record) => $record->deleted_at ? 'Dihapus' : 'Aktif')
                    ->alignCenter(),
            ])
            ->filters([
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
