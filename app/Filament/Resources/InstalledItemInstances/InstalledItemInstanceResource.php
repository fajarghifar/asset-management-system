<?php

namespace App\Filament\Resources\InstalledItemInstances;

use App\Models\Item;
use App\Models\Location;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use App\Models\InstalledItemInstance;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;
use App\Services\InstalledItemInstanceService;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InstalledItemInstances\Pages\ManageInstalledItemInstances;

class InstalledItemInstanceResource extends Resource
{
    protected static ?string $model = InstalledItemInstance::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->label('Jenis Barang')
                    ->relationship(
                        name: 'item',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query
                            ->where('type', 'installed')
                            ->orderBy('name')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Item $record) => "{$record->name} ({$record->code})")
                    ->searchable(['name', 'code'])
                    ->required(),
                TextInput::make('code')
                    ->label('Kode Instance')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(30)
                    ->autofocus(),
                TextInput::make('serial_number')
                    ->label('Nomor Seri')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->nullable(),
                Select::make('installed_location_id')
                    ->label('Lokasi Pemasangan')
                    ->relationship(
                        name: 'installedLocation',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->orderBy('name')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Location $record) => "{$record->name} ({$record->code})")
                    ->searchable(['name', 'code'])
                    ->required(),
                DatePicker::make('installed_at')
                    ->label('Tanggal Pemasangan')
                    ->required(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Barang Terpasang')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('No.')
                    ->rowIndex()
                    ->width('50px'),
                TextColumn::make('code')
                    ->label('Kode Instance')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                TextColumn::make('item.name')
                    ->label('Jenis Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->label('Nomor Seri')
                    ->searchable(),
                TextColumn::make('installedLocation.name')
                    ->label('Lokasi Pemasangan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('installed_at')
                    ->label('Tgl Pemasangan')
                    ->date()
                    ->sortable(),
                IconColumn::make('deleted_at')
                    ->label('Status Data')
                    ->state(fn($record) => !is_null($record->deleted_at))
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-check-circle')
                    ->tooltip(fn(InstalledItemInstance $record) => $record->deleted_at ? 'Dihapus' : 'Aktif'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Instance'),
            ])
            ->filters([
                SelectFilter::make('item')
                    ->relationship(
                        name: 'item',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query
                            ->where('type', 'installed')
                            ->orderBy('name')
                    )
                    ->multiple(),
                TrashedFilter::make()
                    ->label('Status Data')
                    ->placeholder('Hanya data aktif')
                    ->trueLabel('Tampilkan semua data')
                    ->falseLabel('Hanya data yang dihapus'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->iconSize('lg'),
                    EditAction::make()->iconSize('lg'),
                    DeleteAction::make()
                        ->iconSize('lg')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Instance?')
                        ->modalDescription('Instance akan disembunyikan, tapi riwayat lokasi tetap ada.')
                        ->action(function (InstalledItemInstance $record) {
                            try {
                                (new InstalledItemInstanceService())->delete($record);
                                Notification::make()
                                    ->title('Berhasil')
                                    ->body("{$record->code} berhasil dihapus.")
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
                                    (new InstalledItemInstanceService())->delete($record);
                                    $deleted++;
                                } catch (\Exception $e) {
                                    $errors[] = "{$record->code}: " . $e->getMessage();
                                }
                            }
                            if ($deleted > 0) {
                                Notification::make()
                                    ->title("Berhasil menghapus {$deleted} instance")
                                    ->success()
                                    ->send();
                            }
                            if (!empty($errors)) {
                                Notification::make()
                                    ->title('Beberapa instance gagal dihapus')
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
            'index' => ManageInstalledItemInstances::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['item', 'installedLocation'])
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
