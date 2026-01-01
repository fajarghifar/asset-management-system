<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\RelationManagers\RelationManager;

/**
 * Relation Manager for displaying Asset History (Audit Trail).
 * This manager is Read-Only to ensure data integrity.
 */
class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';
    protected static ?string $title = 'Riwayat Aset';

    /**
     * Configure the history table.
     */
    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('executor.name')
                    ->label('PIC / Admin')
                    ->icon('heroicon-m-user')
                    ->formatStateUsing(fn ($state) => $state ?? 'System')
                    ->color('gray'),

                TextColumn::make('action_type')
                    ->label('Aktivitas')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status Aset')
                    ->badge()
                    ->sortable(),

                TextColumn::make('recipient_name')
                    ->label('Peminjam / Penerima')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->searchable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->tooltip(fn(Model $record): string => $record->notes ?? '')
                    ->wrap(),
            ]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
