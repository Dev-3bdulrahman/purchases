<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:super-admin|developer|admin|employee', 'license'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/purchases/suppliers', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Suppliers\Index::class)->name('admin.purchases.suppliers');
        Route::get('/purchases/requests', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Requests\Index::class)->name('admin.purchases.requests');
        Route::get('/purchases/orders', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Orders\Index::class)->name('admin.purchases.orders');
        Route::get('/purchases/invoices', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Invoices\Index::class)->name('admin.purchases.invoices');
        Route::get('/purchases/returns', \Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Returns\Index::class)->name('admin.purchases.returns');
    });
