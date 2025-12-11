<?php

namespace App\Filament\Resources\Borrowings\Pages;

use Filament\Actions\Action;
use App\Services\BorrowingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\Borrowings\BorrowingResource;

class CreateBorrowing extends CreateRecord
{
    protected static string $resource = BorrowingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-m-arrow-left'),
        ];
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $itemsData = $data['items'] ?? [];

        try {
            DB::transaction(function () use ($itemsData) {
                $borrowing = $this->getRecord();
                app(BorrowingService::class)->processItems($borrowing, $itemsData);
            });

            Notification::make()
                ->success()
                ->title('Berhasil')
                ->body('Pengajuan peminjaman berhasil diajukan.')
                ->send();

        } catch (ValidationException $e) {
            Notification::make()->danger()->title('Gagal Mengajukan Peminjaman')->body($e->validator->errors()->first())->persistent()->send();
            $this->getRecord()->delete();
            $this->redirect($this->getResource()::getUrl('create'));
        } catch (\Exception $e) {
            Log::error('Borrowing creation error: ' . $e->getMessage());
            Notification::make()->danger()->title('Kesalahan Sistem')->body('Terjadi kesalahan. Silakan coba lagi.')->persistent()->send();
            $this->getRecord()->delete();
            $this->redirect($this->getResource()::getUrl('create'));
        }
    }
}
