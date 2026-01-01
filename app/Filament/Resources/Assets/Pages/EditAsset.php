<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Models\Asset;
use App\Models\Location;
use App\Enums\AssetStatus;
use Filament\Actions\Action;
use App\Services\AssetService;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Assets\AssetResource;

class EditAsset extends EditRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-m-arrow-left')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),

            ActionGroup::make([
                // PINDAH LOKASI (Move)
                Action::make('move')
                    ->label('Pindah Lokasi')
                    ->icon('heroicon-m-arrows-right-left')
                    ->color('gray')
                    ->form([
                        Select::make('location_id')
                            ->label('Lokasi Baru')
                            ->options(fn() => Location::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Textarea::make('notes')
                            ->label('Alasan Pindah')
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
                    ->action(function (Asset $record, array $data, AssetService $service) {
                        $service->checkOut($record, $data['recipient_name'], $data['notes']);
                        Notification::make()
                            ->success()
                            ->title('Aset diserahkan ke: ' . $data['recipient_name'])
                            ->send();
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
                            ->options(fn() => Location::pluck('name', 'id'))
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
                            Notification::make()
                                ->success()
                                ->title('Aset berhasil dihapus')
                                ->send();
                            return redirect($this->getResource()::getUrl('index'));
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
            ])
            ->button()
            ->hiddenLabel(),
        ];
    }
}
