<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use Openplain\FilamentShadcnTheme\Color;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Resources\Areas\AreaResource;
use App\Filament\Resources\Items\ItemResource;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Filament\Resources\Locations\LocationResource;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Resources\Borrowings\BorrowingResource;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Filament\Resources\InstalledItems\InstalledItemResource;
use App\Filament\Resources\InventoryItems\InventoryItemResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->spa()
            // ->maxContentWidth(Width::Full)
            ->topNavigation()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Inventaris')
                    ->icon('heroicon-o-archive-box')
                    ->collapsible(),
                NavigationGroup::make()
                    ->label('Lokasi')
                    ->icon('heroicon-o-map-pin')
                    ->collapsible(),
            ])
            ->navigationItems([
                NavigationItem::make('Master Barang')
                    ->url(fn(): string => ItemResource::getUrl('index'))
                    ->group('Inventaris')
                    ->sort(1)
                    ->isActiveWhen(fn() => request()->routeIs(ItemResource::getRouteBaseName() . '*')),
                NavigationItem::make('Daftar Aset Inventaris')
                    ->url(fn(): string => InventoryItemResource::getUrl('index'))
                    ->group('Inventaris')
                    ->sort(2)
                    ->isActiveWhen(fn() => request()->routeIs(InventoryItemResource::getRouteBaseName() . '*')),
                NavigationItem::make('Daftar Aset Terpasang')
                    ->url(fn(): string => InstalledItemResource::getUrl('index'))
                    ->group('Inventaris')
                    ->sort(4)
                    ->isActiveWhen(fn() => request()->routeIs(InstalledItemResource::getRouteBaseName() . '*')),
                NavigationItem::make('Peminjaman')
                    ->url(fn(): string => BorrowingResource::getUrl('index'))
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->isActiveWhen(fn() => request()->routeIs(BorrowingResource::getRouteBaseName() . '*')),
                NavigationItem::make('Area')
                    ->url(fn(): string => AreaResource::getUrl('index'))
                    ->group('Lokasi')
                    ->sort(1)
                    ->isActiveWhen(fn() => request()->routeIs(AreaResource::getRouteBaseName() . '*')),
                NavigationItem::make('Lokasi')
                    ->url(fn(): string => LocationResource::getUrl('index'))
                    ->group('Lokasi')
                    ->sort(2)
                    ->isActiveWhen(fn() => request()->routeIs(LocationResource::getRouteBaseName() . '*')),
            ])
            ->breadcrumbs(false)
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
