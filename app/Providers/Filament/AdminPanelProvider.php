<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Pages\CreateBorrowingPage;
use App\Filament\Resources\Areas\AreaResource;
use App\Filament\Resources\Items\ItemResource;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Filament\Resources\Locations\LocationResource;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Resources\Borrowings\BorrowingResource;
use App\Filament\Resources\ItemStocks\ItemStockResource;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Filament\Resources\FixedItemInstances\FixedItemInstanceResource;
use App\Filament\Resources\InstalledItemInstances\InstalledItemInstanceResource;

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
            ->sidebarCollapsibleOnDesktop()
            // ->maxContentWidth(Width::Full)
            ->topNavigation()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Peminjaman')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->collapsible(),
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
                NavigationItem::make('Stok Barang Habis Pakai')
                    ->url(fn(): string => ItemStockResource::getUrl('index'))
                    ->group('Inventaris')
                    ->sort(2)
                    ->isActiveWhen(fn() => request()->routeIs(ItemStockResource::getRouteBaseName() . '*')),
                NavigationItem::make('Instance Barang Tetap')
                    ->url(fn(): string => FixedItemInstanceResource::getUrl('index'))
                    ->group('Inventaris')
                    ->sort(3)
                    ->isActiveWhen(fn() => request()->routeIs(FixedItemInstanceResource::getRouteBaseName() . '*')),
                NavigationItem::make('Instance Barang Terpasang')
                    ->url(fn(): string => InstalledItemInstanceResource::getUrl('index'))
                    ->group('Inventaris')
                    ->sort(4)
                    ->isActiveWhen(fn() => request()->routeIs(InstalledItemInstanceResource::getRouteBaseName() . '*')),
                NavigationItem::make('Ajukan Peminjaman')
                    ->url(fn(): string => CreateBorrowingPage::getUrl())
                    ->group('Peminjaman')
                    ->sort(1)
                    ->isActiveWhen(fn() => request()->routeIs(CreateBorrowingPage::getRouteName())),
                NavigationItem::make('Daftar Peminjaman')
                    ->url(fn(): string => BorrowingResource::getUrl('index'))
                    ->group('Peminjaman')
                    ->sort(2)
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
