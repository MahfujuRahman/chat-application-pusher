<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Modules\Management\WarehouseManagement\WarehouseProductOut\Models\WareHouseProductOutProductModel as WarehouseProductOut;
use App\Modules\Management\WarehouseManagement\WarehouseProductOut\Observer\WarehouseProductOutObserver;

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
        Paginator::useBootstrap();
        // WarehouseProductOut::observe(WarehouseProductOutObserver::class);
    }
}
