<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Models\Asset;
use App\Models\Location;
use App\Enums\AssetAction;
use App\Enums\AssetStatus;
use Filament\Tables\Table;
use App\Enums\LocationSite;
use Filament\Actions\Action;
use App\Models\AssetHistory;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Aset')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('asset_tag')
                    ->label('Tag ID')
                    ->searchable()
                    ->copyable()
                    ->color('primary')
                    ->weight('medium'),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->label('Nomor Seri')
                    ->searchable()
                    ->fontFamily('mono')
                    ->color('gray'),
                TextColumn::make('location.site')
                    ->label('Site')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('latestHistory.recipient_name')
                    ->label('Peminjam')
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Aset'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(AssetStatus::class)
                    ->searchable()
                    ->native(false),

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

                    // PINDAH LOKASI (Move)
                    Action::make('move')
                        ->label('Pindah Lokasi')
                        ->icon('heroicon-m-arrows-right-left')
                        ->color('gray')
                        ->form([
                            Select::make('location_id')
                                ->label('Lokasi Baru')
                                ->options(Location::all()->pluck('full_name', 'id'))
                                ->searchable()
                                ->required(),
                            Textarea::make('notes')
                                ->label('Alasan Pindah')
                                ->required(),
                        ])
                        ->action(function (Asset $record, array $data) {
                            $record->shouldLogHistory = false;
                            $record->update(['location_id' => $data['location_id']]);

                            AssetHistory::create([
                                'asset_id' => $record->id,
                                'user_id' => Auth::id(),
                                'status' => $record->status,
                                'location_id' => $data['location_id'],
                                'action_type' => AssetAction::Move,
                                'notes' => $data['notes'],
                            ]);

                            Notification::make()->success()->title('Lokasi Berhasil Dipindah')->send();
                        }),

                    // PEMINJAMAN (Check-Out)
                    Action::make('check_out')
                        ->label('Pinjamkan / Serahkan')
                        ->icon('heroicon-m-arrow-up-tray')
                        ->color('info')
                        ->visible(fn (Asset $record) => $record->status === AssetStatus::InStock)
                        ->form([
                            TextInput::make('recipient_name')
                                ->label('Nama Peminjam / Penerima')
                                ->placeholder('Contoh: IT - Dimas atau Vendor CCTV')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('notes')
                                ->label('Keperluan')
                                ->required(),
                        ])
                        ->action(function (Asset $record, array $data) {
                            $record->shouldLogHistory = false;
                            $record->update(['status' => AssetStatus::Loaned]);

                            AssetHistory::create([
                                'asset_id'       => $record->id,
                                'user_id'        => Auth::id(),
                                'recipient_name' => $data['recipient_name'],
                                'status'         => AssetStatus::Loaned,
                                'location_id'    => $record->location_id,
                                'action_type'    => AssetAction::CheckOut,
                                'notes'          => $data['notes'],
                            ]);

                            Notification::make()->success()->title('Aset diserahkan ke: ' . $data['recipient_name'])->send();
                        }),

                    // PENGEMBALIAN (Check-In)
                    Action::make('check_in')
                        ->label('Kembalikan (Check-In)')
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
                        ->action(function (Asset $record, array $data) {
                            $record->shouldLogHistory = false;
                            $record->update([
                                'status' => AssetStatus::InStock,
                                'location_id' => $data['location_id']
                            ]);

                            AssetHistory::create([
                                'asset_id' => $record->id,
                                'user_id' => Auth::id(),
                                'status' => AssetStatus::InStock,
                                'location_id' => $data['location_id'],
                                'action_type' => AssetAction::CheckIn,
                                'notes' => $data['notes'],
                            ]);

                            Notification::make()->success()->title('Aset Dikembalikan')->send();
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
                //
            ]);
    }
}
