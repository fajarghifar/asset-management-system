<?php

namespace App\Providers;

use App\Models\Item;
use App\Observers\ItemObserver;
use App\Models\FixedItemInstance;
use App\Models\InstalledItemInstance;
use Illuminate\Support\ServiceProvider;
use App\Observers\FixedItemInstanceObserver;
use App\Observers\InstalledItemInstanceObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Item::observe(ItemObserver::class);
        FixedItemInstance::observe(FixedItemInstanceObserver::class);
        InstalledItemInstance::observe(InstalledItemInstanceObserver::class);
    }
}
