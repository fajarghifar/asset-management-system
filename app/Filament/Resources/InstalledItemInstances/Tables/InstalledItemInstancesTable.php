<?php

namespace App\Filament\Resources\InstalledItemInstances\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use App\Models\InstalledItemInstance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;
use App\Services\InstalledItemInstanceService;

class InstalledItemInstancesTable
{
    public static function configure(Table $table): Table
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
                ])
                ->dropdownPlacement('left-start'),
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
}
