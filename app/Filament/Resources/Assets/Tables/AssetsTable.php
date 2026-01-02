<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Models\Asset;
use App\Models\Location;
use App\Enums\AssetStatus;
use Filament\Tables\Table;
use App\Enums\LocationSite;
use Filament\Actions\Action;
use App\Services\AssetService;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use EightyNine\ExcelImport\ExcelImportAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;

class AssetsTable
{
    /**
     * Configure the table columns and filters.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->heading(fn() => __('resources.assets.plural_label'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('asset_tag')
                    ->label(__('resources.assets.fields.asset_tag'))
                    ->searchable()
                    ->copyable()
                    ->color('primary')
                    ->weight('medium'),
                TextColumn::make('product.name')
                    ->label(__('resources.assets.fields.product'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('serial_number')
                    ->label(__('resources.assets.fields.serial_number'))
                    ->searchable()
                    ->fontFamily('mono')
                    ->color('gray')
                    ->placeholder('-'),

                TextColumn::make('location.site')
                    ->label('Site')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label(__('resources.assets.fields.location'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('latestHistory.recipient_name')
                    ->label(__('resources.assets.fields.recipient_name'))
                    ->placeholder('-')
                    ->limit(20),
            ])
            ->headerActions([
                ExcelImportAction::make()
                    ->label('Import Aset')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->use(\App\Imports\AssetImport::class)
                    ->validateUsing([
                        'product_name' => 'required',
                        'location_name' => 'required',
                    ])
                    ->sampleExcel(
                        sampleData: [
                            [
                                'product_name' => 'Laptop Dell Latitude',
                                'product_code' => 'LPT-DELL-01',
                                'location_name' => 'Ruang IT',
                                'asset_tag' => 'AST-IT-001',
                                'serial_number' => 'SN12345678',
                                'status' => 'Tersedia',
                                'purchase_date' => '2025-01-01',
                                'purchase_price' => 15000000,
                                'supplier' => 'CV. Tech Solution',
                                'notes' => 'Pengadaan Awal Tahun',
                            ],
                            [
                                'product_name' => 'Kursi Kerja Ergonomis',
                                'product_code' => '',
                                'location_name' => 'Lobby Utama',
                                'asset_tag' => '',
                                'serial_number' => '',
                                'status' => 'Dipinjam',
                                'purchase_date' => '2025-02-15',
                                'purchase_price' => 2500000,
                                'supplier' => 'IKEA Business',
                                'notes' => '',
                            ],
                        ],
                        fileName: 'template_import_aset.xlsx',
                        sampleButtonLabel: 'Download Template',
                        customiseActionUsing: fn($action) => $action
                            ->color('info')
                            ->icon('heroicon-o-document-arrow-down')
                    ),

                FilamentExportHeaderAction::make('export')
                    ->label('Export Aset')
                    ->color('gray')
                    ->defaultPageOrientation('landscape')
                    ->fileName('Data_Aset_' . date('Y-m-d'))
                    ->defaultFormat('xlsx'),

                CreateAction::make()->label(__('resources.general.actions.create') ?? 'Tambah Aset'), // Need to add general create? Leaving fallback
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(AssetStatus::class)
                    ->searchable()
                    ->native(false),

                // Advanced Location Filter
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
                                    ->when($get('site'), fn ($q) => $q->whereIn('site', $get('site')))
                                    ->pluck('name', 'id')
                            ),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['site'], fn ($q) => $q->whereHas('location', fn ($l) => $l->whereIn('site', $data['site'])))
                            ->when($data['location_id'], fn ($q) => $q->whereIn('location_id', $data['location_id']));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    // --- MOVE ACTION ---
                    Action::make('move')
                        ->label(__('resources.assets.actions.move'))
                        ->icon('heroicon-m-arrows-right-left')
                        ->color('gray')
                        ->form([
                            Select::make('location_id')
                                ->label(__('resources.assets.fields.location'))
                                ->options(fn() => Location::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Textarea::make('notes')
                                ->label(__('resources.assets.fields.notes'))
                                ->required(),
                        ])
                        ->action(function (Asset $record, array $data, AssetService $service) {
                            $service->move($record, $data['location_id'], $data['notes']);
                            Notification::make()
                                ->success()
                                ->title('Lokasi Berhasil Dipindah')
                                ->send();
                        }),

                    // PEMINJAMAN (Check-Out)
                    Action::make('check_out')
                        ->label(__('resources.assets.actions.check_out'))
                        ->icon('heroicon-m-arrow-up-tray')
                        ->color('info')
                        ->visible(fn (Asset $record) => $record->status === AssetStatus::InStock)
                        ->form([
                            TextInput::make('recipient_name')
                                ->label(__('resources.assets.fields.recipient_name'))
                                ->placeholder('Contoh: IT - Dimas atau Vendor CCTV')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('notes')
                                ->label(__('resources.assets.fields.notes'))
                                ->required(),
                        ])
                        ->action(function (Asset $record, array $data, AssetService $service) {
                            $service->checkOut($record, $data['recipient_name'], $data['notes']);
                            Notification::make()
                                ->success()
                                ->title('Aset diserahkan ke: ' . $data['recipient_name'])
                                ->send();
                        }),

                    // PENGEMBALIAN (Check-In)
                    Action::make('check_in')
                        ->label(__('resources.assets.actions.check_in'))
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->visible(fn (Asset $record) => $record->status === AssetStatus::Loaned)
                        ->form([
                            Select::make('location_id')
                                ->label('Kembali ke Lokasi')
                                ->options(Location::pluck('name', 'id'))
                                ->default(fn(Asset $record) => $record->location_id)
                                ->required(),
                            Textarea::make('notes')
                                ->label('Kondisi Pengembalian')
                                ->required(),
                        ])
                        ->action(function (Asset $record, array $data, AssetService $service) {
                            $service->checkIn($record, $data['location_id'], $data['notes']);
                            Notification::make()
                                ->success()
                                ->title('Aset Dikembalikan')
                                ->send();
                        }),

                    DeleteAction::make()
                        ->modalDescription('Apakah Anda yakin ingin menghapus aset ini secara permanen?')
                        ->action(function (Asset $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Aset berhasil dihapus')->send();
                            } catch (\Illuminate\Database\QueryException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Menghapus')
                                    ->body('Aset tidak bisa dihapus karena masih terikat data lain.')
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Error Sistem')
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),
                ])->dropdownPlacement('left-start')
            ])
            ->toolbarActions([
                FilamentExportBulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-arrow-down-tray')
            ]);
    }
}
