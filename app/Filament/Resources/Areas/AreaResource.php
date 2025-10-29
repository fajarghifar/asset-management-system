<?php

namespace App\Filament\Resources\Areas;

use App\Models\Area;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Areas\Pages\ManageAreas;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Area')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->helperText('Contoh: PH-A, OFF-B, STORE-C')
                    ->autofocus(),
                TextInput::make('name')
                    ->label('Nama Area')
                    ->required()
                    ->maxLength(100),
                Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'housing' => 'Perumahan',
                        'office' => 'Kantor',
                        'store' => 'Store',
                    ])
                    ->required()
                    ->native(false),
                Textarea::make('address')
                    ->label('Alamat')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Area')
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('No.')
                    ->rowIndex()
                    ->width('50px'),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Area')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'housing' => 'info',
                        'office' => 'success',
                        'store' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'housing' => 'Perumahan',
                        'office' => 'Kantor',
                        'store' => 'Store',
                        default => $state,
                    }),
                TextColumn::make('locations_count')
                    ->label('Jumlah Lokasi')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn($state) => $state > 0 ? "{$state} lokasi" : 'Belum ada lokasi'),
                TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(30),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Area'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->iconSize('lg'),
                    EditAction::make()->iconSize('lg'),
                    DeleteAction::make()
                        ->iconSize('lg')
                        ->visible(fn(Area $record) => $record->locations_count === 0)
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Area?')
                        ->modalDescription('Area yang tidak memiliki lokasi bisa dihapus. Tindakan ini tidak bisa dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->action(function (Area $record) {
                            try {
                                $record->delete();
                                Notification::make()
                                    ->title('Area Dihapus')
                                    ->body("Area \"{$record->name}\" berhasil dihapus.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal Menghapus Area')
                                    ->body('Pastikan area tidak digunakan di lokasi lain.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])->dropdownPlacement('left-start'),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make()
                //         ->deselectRecordsAfterCompletion(false)
                //         ->action(function (Collection $records) {
                //             $deleted = 0;
                //             $errors = [];
                //             foreach ($records as $record) {
                //                 if ($record->locations_count === 0) {
                //                     try {
                //                         $record->delete();
                //                         $deleted++;
                //                     } catch (\Exception $e) {
                //                         $errors[] = "Gagal menghapus {$record->name}";
                //                     }
                //                 } else {
                //                     $errors[] = "Tidak bisa menghapus {$record->name}: masih memiliki lokasi.";
                //                 }
                //             }
                //             if ($deleted > 0) {
                //                 Notification::make()
                //                     ->title("Berhasil menghapus {$deleted} area")
                //                     ->success()
                //                     ->send();
                //             }
                //             if (!empty($errors)) {
                //                 Notification::make()
                //                     ->title('Beberapa area gagal dihapus')
                //                     ->body(implode('\n', $errors))
                //                     ->danger()
                //                     ->send();
                //             }
                //         }),
                // ]),
            ])
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAreas::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('locations');
    }
}
