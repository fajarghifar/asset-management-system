<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Auth\Login;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Widgets\LowStockWidget;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Widgets\LatestLoansWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Widgets\AssetStatusChartWidget;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Widgets\MonthlyLoanChartWidget;
use App\Filament\Widgets\TopProductsChartWidget;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
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
                StatsOverviewWidget::class,
                AssetStatusChartWidget::class,
                MonthlyLoanChartWidget::class,
                TopProductsChartWidget::class,
                LatestLoansWidget::class,
                LowStockWidget::class,
                // AccountWidget::class,
                // FilamentInfoWidget::class,
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
                    ->label(fn() => __('resources.navigation_groups.loans'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible(),
                NavigationGroup::make()
                    ->label(fn() => __('resources.navigation_groups.inventory'))
                    ->icon('heroicon-o-archive-box')
                    ->collapsible(),
                NavigationGroup::make()
                    ->label(fn() => __('resources.navigation_groups.location'))
                    ->icon('heroicon-o-map')
                    ->collapsible(),
                NavigationGroup::make()
                    ->label(fn() => __('resources.navigation_groups.settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible(),
            ])
            ->breadcrumbs(false)
            ->plugins([
                FilamentApexChartsPlugin::make(),
            ]);
    }
}
