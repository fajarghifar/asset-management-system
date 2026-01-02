<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Enums\ProductType;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\QueryException;
use Filament\Tables\Filters\TernaryFilter;
use EightyNine\ExcelImport\ExcelImportAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;

class ProductsTable
{
    /**
     * Configure the main table to display products.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Data Barang')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('code')
                    ->label('Kode Barang')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Kategori Barang')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('Tipe Barang')
                    ->sortable()
                    ->badge(),

                // Use the Model accessor `total_stock`
                // This works efficiently because we use `scopeWithStock()` in the Resource
                TextColumn::make('total_stock')
                    ->label('Total Stok')
                    ->formatStateUsing(function (Product $record, $state) {
                        $unit = $record->type === ProductType::Asset ? 'Unit' : 'Pcs';
                        return "{$state} {$unit}";
                    })
                    ->badge()
                    ->color(fn($state) => (int) $state > 0 ? 'success' : 'danger')
                    ->alignCenter(),

                IconColumn::make('can_be_loaned')
                    ->label('Status Pinjam')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->headerActions([
                ExcelImportAction::make()
                    ->label('Import Excel')
                    ->color('gray')
                    ->icon(icon: 'heroicon-o-arrow-up-tray')
                    ->use(ProductImport::class)
                    ->validateUsing([
                        'nama_produk' => 'required',
                        'kategori' => 'required',
                        'tipe' => 'required',
                    ])
                    ->sampleExcel(
                        sampleData: [
                            [
                                'nama_produk' => 'Laptop Lenovo Thinkpad',
                                'kode_barang' => 'LPT-THINK-01',
                                'kategori' => 'Elektronik',
                                'tipe' => 'asset',
                                'deskripsi' => 'Laptop untuk staff IT',
                            ],
                            [
                                'nama_produk' => 'Kertas A4 Sidu',
                                'kode_barang' => 'ATK-A4-001',
                                'kategori' => 'ATK',
                                'tipe' => 'consumable',
                                'deskripsi' => 'Kertas HVS 70gsm',
                            ],
                        ],
                        fileName: 'template_import_produk.xlsx',
                        sampleButtonLabel: 'Download Template',
                        customiseActionUsing: fn($action) => $action
                            ->color('info')
                            ->icon('heroicon-o-document-arrow-down')
                    ),

                FilamentExportHeaderAction::make('export')
                    ->label('Export Data')
                    ->color('gray')
                    ->defaultPageOrientation('landscape')
                    ->disableAdditionalColumns()
                    ->fileName(date('Y-m-d_H-i'))
                    ->defaultFormat('xlsx')
                    ->disableAdditionalColumns(),

                CreateAction::make()->label('Tambah Barang'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('type')
                    ->label('Jenis Barang')
                    ->options(ProductType::class)
                    ->native(false),

                TernaryFilter::make('can_be_loaned')
                    ->label('Status Peminjaman')
                    ->native(false)
                    ->placeholder('Semua')
                    ->trueLabel('Bisa Dipinjam')
                    ->falseLabel('Tidak Bisa Dipinjam'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->modalHeading('Hapus Produk')
                        ->modalDescription('Apakah Anda yakin? Data akan dihapus PERMANEN dan tidak dapat dikembalikan.')
                        ->action(function (Product $record) {
                            try {
                                $record->delete();
                                Notification::make()
                                    ->success()
                                    ->title('Produk berhasil dihapus')
                                    ->send();
                            } catch (QueryException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Menghapus')
                                    ->body('Produk ini tidak bisa dihapus karena memiliki data terkait (Aset/Stok/Peminjaman).')
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Error Sistem')
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                FilamentExportBulkAction::make('export')
                    ->label('Export Data')
                    ->color('gray')
                    ->defaultPageOrientation('landscape')
                    ->disableAdditionalColumns()
                    ->fileName(date('Y-m-d_H-i'))
                    ->defaultFormat('xlsx')
                    ->defaultPageOrientation('landscape')
                    ->disableAdditionalColumns(),
            ]);
    }
}
