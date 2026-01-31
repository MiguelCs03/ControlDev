<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Registrar observers para sincronización automática de permisos
        \App\Models\Menu::observe(\App\Observers\MenuObserver::class);
        \App\Models\Submenu::observe(\App\Observers\SubmenuObserver::class);
        \App\Models\Subsubmenu::observe(\App\Observers\SubsubmenuObserver::class);
    }
}
