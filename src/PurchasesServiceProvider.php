<?php

namespace Dev3bdulrahman\Purchases;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Dev3bdulrahman\Purchases\Events\GoodsReceiptCompleted;
use Dev3bdulrahman\Purchases\Events\GoodsReceiptCreated;
use Dev3bdulrahman\Purchases\Events\PurchaseOrderApproved;
use Dev3bdulrahman\Purchases\Events\SupplierPaymentMade;
use Dev3bdulrahman\Purchases\Listeners\LogGoodsReceipt;
use Dev3bdulrahman\Purchases\Listeners\LogPurchaseOrderApproval;
use Dev3bdulrahman\Purchases\Listeners\UpdatePurchaseOrderOnReceipt;
use Dev3bdulrahman\Purchases\Listeners\UpdateSupplierInvoiceOnPayment;
use Dev3bdulrahman\Purchases\Models\GoodsReceiptNote;
use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Dev3bdulrahman\Purchases\Models\PurchaseRequest;
use Dev3bdulrahman\Purchases\Models\PurchaseReturn;
use Dev3bdulrahman\Purchases\Models\Supplier;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
use Dev3bdulrahman\Purchases\Models\SupplierPayment;
use Dev3bdulrahman\Purchases\Policies\GoodsReceiptNotePolicy;
use Dev3bdulrahman\Purchases\Policies\PurchaseOrderPolicy;
use Dev3bdulrahman\Purchases\Policies\PurchaseRequestPolicy;
use Dev3bdulrahman\Purchases\Policies\PurchaseReturnPolicy;
use Dev3bdulrahman\Purchases\Policies\SupplierInvoicePolicy;
use Dev3bdulrahman\Purchases\Policies\SupplierPaymentPolicy;
use Dev3bdulrahman\Purchases\Policies\SupplierPolicy;
use Dev3bdulrahman\Purchases\Policies\ThreeWayMatchPolicy;

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

        // Register Policies
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(PurchaseRequest::class, PurchaseRequestPolicy::class);
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(SupplierInvoice::class, SupplierInvoicePolicy::class);
        Gate::policy(SupplierPayment::class, SupplierPaymentPolicy::class);
        Gate::policy(PurchaseReturn::class, PurchaseReturnPolicy::class);
        Gate::policy(GoodsReceiptNote::class, GoodsReceiptNotePolicy::class);
        Gate::define('purchases.matching.view', [ThreeWayMatchPolicy::class, 'viewAny']);
        Gate::define('purchases.matching.resolve', [ThreeWayMatchPolicy::class, 'resolve']);

        // Register Event Listeners
        Event::listen(PurchaseOrderApproved::class, LogPurchaseOrderApproval::class);
        Event::listen(SupplierPaymentMade::class, UpdateSupplierInvoiceOnPayment::class);
        Event::listen(GoodsReceiptCreated::class, LogGoodsReceipt::class);
        Event::listen(GoodsReceiptCompleted::class, LogGoodsReceipt::class);
        Event::listen(GoodsReceiptCompleted::class, UpdatePurchaseOrderOnReceipt::class);

        // Register Livewire Components
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('purchases-suppliers-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Suppliers\Index::class);
            \Livewire\Livewire::component('purchases-requests-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Requests\Index::class);
            \Livewire\Livewire::component('purchases-orders-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Orders\Index::class);
            \Livewire\Livewire::component('purchases-invoices-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Invoices\Index::class);
            \Livewire\Livewire::component('purchases-returns-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Returns\Index::class);
            \Livewire\Livewire::component('purchases-goods-receipt-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\GoodsReceipt\Index::class);
            \Livewire\Livewire::component('purchases-matching-index', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Matching\Index::class);
        }
    }
}
