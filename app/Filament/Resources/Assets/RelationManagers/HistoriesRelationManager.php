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
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.assets.history.title');
    }

    /**
     * Configure the history table.
     */
    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('resources.assets.history.time'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('executor.name')
                    ->label(__('resources.assets.history.pic'))
                    ->icon('heroicon-m-user')
                    ->formatStateUsing(fn ($state) => $state ?? 'System')
                    ->color('gray'),

                TextColumn::make('action_type')
                    ->label(__('resources.assets.history.activity'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('resources.assets.history.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('recipient_name')
                    ->label(__('resources.assets.history.recipient'))
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('location.site')
                    ->label(__('resources.assets.history.site'))
                    ->searchable(),

                TextColumn::make('location.name')
                    ->label(__('resources.assets.history.location'))
                    ->searchable(),

                TextColumn::make('notes')
                    ->label(__('resources.assets.history.notes'))
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
