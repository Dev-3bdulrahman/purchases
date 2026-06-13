<?php

namespace Dev3bdulrahman\Purchases;

use Illuminate\Support\ServiceProvider;

class PurchasesServiceProvider extends ServiceProvider
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
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');

        // Load package views
        $this->loadViewsFrom(__DIR__ . '/Views', 'purchases');

        // Load package translations
        $this->loadTranslationsFrom(__DIR__ . '/Translations', 'purchases');

        // Register Livewire Components
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('purchases-suppliers-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Suppliers\Index::class);
            \Livewire\Livewire::component('purchases-requests-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Requests\Index::class);
            \Livewire\Livewire::component('purchases-orders-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Orders\Index::class);
            \Livewire\Livewire::component('purchases-invoices-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Invoices\Index::class);
            \Livewire\Livewire::component('purchases-returns-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Returns\Index::class);
        }
    }
}
