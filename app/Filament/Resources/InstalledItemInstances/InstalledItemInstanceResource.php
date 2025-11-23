<?php

namespace App\Filament\Resources\InstalledItemInstances;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\InstalledItemInstance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InstalledItemInstances\Pages\EditInstalledItemInstance;
use App\Filament\Resources\InstalledItemInstances\Pages\ViewInstalledItemInstance;
use App\Filament\Resources\InstalledItemInstances\Pages\ListInstalledItemInstances;
use App\Filament\Resources\InstalledItemInstances\Pages\CreateInstalledItemInstance;
use App\Filament\Resources\InstalledItemInstances\Schemas\InstalledItemInstanceForm;
use App\Filament\Resources\InstalledItemInstances\Tables\InstalledItemInstancesTable;
use App\Filament\Resources\InstalledItemInstances\Schemas\InstalledItemInstanceInfolist;
use App\Filament\Resources\InstalledItemInstances\RelationManagers\LocationHistoryRelationManager;

class InstalledItemInstanceResource extends Resource
{
    protected static ?string $model = InstalledItemInstance::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return InstalledItemInstanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstalledItemInstanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstalledItemInstancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LocationHistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstalledItemInstances::route('/'),
            'create' => CreateInstalledItemInstance::route('/create'),
            'view' => ViewInstalledItemInstance::route('/{record}'),
            'edit' => EditInstalledItemInstance::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['item', 'currentLocation'])
            ->withTrashed();
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
