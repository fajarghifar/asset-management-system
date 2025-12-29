<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Auth\Login;
use Filament\Pages\Dashboard;
use Filament\Support\Enums\Width;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Resources\Areas\AreaResource;
use App\Filament\Resources\Assets\AssetResource;
use App\Filament\Resources\Categories\CategoryResource;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Filament\Resources\Locations\LocationResource;
use App\Filament\Resources\Products\ProductResource;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
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
            ])
            ->navigationItems([
                NavigationItem::make('Master Barang')
                    ->url(fn(): string => ProductResource::getUrl('index'))
                    ->group('Inventaris')
                    ->sort(1)
                    ->isActiveWhen(fn() => request()->routeIs(ProductResource::getRouteBaseName() . '*')),
                NavigationItem::make('Daftar Aset')
                    ->url(fn(): string => AssetResource::getUrl('index'))
                    ->group('Inventaris')
                    ->sort(2)
                    ->isActiveWhen(fn() => request()->routeIs(AssetResource::getRouteBaseName() . '*')),
                NavigationItem::make('Kategori Barang')
                    ->url(fn(): string => CategoryResource::getUrl('index'))
                    ->group('Inventaris')
                    ->sort(3)
                    ->isActiveWhen(fn() => request()->routeIs(CategoryResource::getRouteBaseName() . '*')),
                // NavigationItem::make('Daftar Aset Inventaris')
                //     ->url(fn(): string => InventoryItemResource::getUrl('index'))
                //     ->group('Inventaris')
                //     ->sort(2)
                //     ->isActiveWhen(fn() => request()->routeIs(InventoryItemResource::getRouteBaseName() . '*')),
                // NavigationItem::make('Daftar Aset Terpasang')
                //     ->url(fn(): string => InstalledItemResource::getUrl('index'))
                //     ->group('Inventaris')
                //     ->sort(4)
                //     ->isActiveWhen(fn() => request()->routeIs(InstalledItemResource::getRouteBaseName() . '*')),
                // NavigationItem::make('Peminjaman')
                //     ->url(fn(): string => BorrowingResource::getUrl('index'))
                //     ->icon('heroicon-o-inbox-arrow-down')
                //     ->isActiveWhen(fn() => request()->routeIs(BorrowingResource::getRouteBaseName() . '*')),
            ])
            ->breadcrumbs(false);
    }
}
