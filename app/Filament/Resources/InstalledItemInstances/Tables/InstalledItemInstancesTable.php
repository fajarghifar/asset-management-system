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
use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;

class InstalledItemInstancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Aset Terpasang')
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
                    ->tooltip(fn(InstalledItemInstance $record) => $record->deleted_at ? 'Dihapus' : 'Aktif')
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Barang'),
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
                SelectFilter::make('area')
                    ->label('Area')
                    ->relationship('currentLocation.area', 'name'),
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
                        ->modalHeading('Hapus Instance?')
                        ->modalDescription('Instance akan disembunyikan (Soft Delete), riwayat lokasi tetap tersimpan.'),

                    ForceDeleteAction::make()->iconSize('lg'),
                    RestoreAction::make()->iconSize('lg'),
                ])
                    ->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                //
            ])
            ->striped();
    }
}
