<?php

namespace App\Filament\Resources\Borrowings\Pages;

use Filament\Actions\Action;
use App\Services\BorrowingService;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
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

    protected function handleRecordCreation(array $data): Model
    {
        $itemsData = $data['items'] ?? [];

        unset($data['items']);

        try {
            $service = app(BorrowingService::class);

            return $service->create($data, $itemsData);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->danger()
                ->title('Gagal Membuat Peminjaman')
                ->body($e->validator->errors()->first())
                ->persistent()
                ->send();

            $this->halt();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Terjadi Kesalahan Sistem')
                ->body($e->getMessage())
                ->persistent()
                ->send();

            $this->halt();
        }

        throw new \RuntimeException('Failed to create Borrowing record.');
    }
}
