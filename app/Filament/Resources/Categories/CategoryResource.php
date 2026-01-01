<?php

namespace App\Filament\Resources\Categories;

use UnitEnum;
use App\Models\Category;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\QueryException;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Categories\Pages\ManageCategories;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Kategori Barang';
    protected static ?string $pluralModelLabel = 'Kategori Barang';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($get, $set, ?string $old, ?string $state) {
                        if (($get('slug') ?? '') !== Str::slug($old ?? '')) {
                            return;
                        }
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->helperText('Otomatis diisi dari nama. Ubah jika perlu kustomisasi URL.'),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Kategori Barang')
            ->modifyQueryUsing(fn(Builder $query) => $query->withCount('products'))
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->color('gray')
                    ->fontFamily('mono'),
                TextColumn::make('products_count')
                    ->label('Total Barang')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'info' : 'gray')
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Kategori'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->modalDescription('Pastikan kategori ini tidak memiliki produk.')
                        ->action(function (Category $record) {
                            try {
                                $record->delete();
                                Notification::make()->success()->title('Kategori dihapus')->send();
                            } catch (QueryException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Menghapus')
                                    ->body('Kategori ini sedang digunakan oleh Produk. Hapus atau pindahkan produk terlebih dahulu.')
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

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
