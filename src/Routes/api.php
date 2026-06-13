<?php

use Illuminate\Support\Facades\Route;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\SupplierApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\PurchaseRequestApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\PurchaseOrderApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\SupplierInvoiceApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\SupplierPaymentApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\PurchaseReturnApiController;

Route::prefix('api/v1/purchases')->middleware(['api', 'auth'])->group(function () {
    // Suppliers
    Route::get('suppliers', [SupplierApiController::class, 'index'])->middleware('can:purchases.suppliers.view');
    Route::post('suppliers', [SupplierApiController::class, 'store'])->middleware('can:purchases.suppliers.create');
    Route::get('suppliers/{id}', [SupplierApiController::class, 'show'])->middleware('can:purchases.suppliers.view');
    Route::put('suppliers/{id}', [SupplierApiController::class, 'update'])->middleware('can:purchases.suppliers.edit');
    Route::delete('suppliers/{id}', [SupplierApiController::class, 'destroy'])->middleware('can:purchases.suppliers.delete');

    // Purchase Requests
    Route::get('requests', [PurchaseRequestApiController::class, 'index'])->middleware('can:purchases.requests.view');
    Route::post('requests', [PurchaseRequestApiController::class, 'store'])->middleware('can:purchases.requests.create');
    Route::get('requests/{id}', [PurchaseRequestApiController::class, 'show'])->middleware('can:purchases.requests.view');
    Route::put('requests/{id}', [PurchaseRequestApiController::class, 'update'])->middleware('can:purchases.requests.edit');
    Route::delete('requests/{id}', [PurchaseRequestApiController::class, 'destroy'])->middleware('can:purchases.requests.delete');
    Route::post('requests/{id}/convert', [PurchaseRequestApiController::class, 'convertToOrder'])->middleware('can:purchases.orders.create');

    // Purchase Orders
    Route::get('orders', [PurchaseOrderApiController::class, 'index'])->middleware('can:purchases.orders.view');
    Route::post('orders', [PurchaseOrderApiController::class, 'store'])->middleware('can:purchases.orders.create');
    Route::get('orders/{id}', [PurchaseOrderApiController::class, 'show'])->middleware('can:purchases.orders.view');
    Route::put('orders/{id}', [PurchaseOrderApiController::class, 'update'])->middleware('can:purchases.orders.edit');
    Route::delete('orders/{id}', [PurchaseOrderApiController::class, 'destroy'])->middleware('can:purchases.orders.delete');
    Route::post('orders/{id}/convert-to-invoice', [PurchaseOrderApiController::class, 'convertToInvoice'])->middleware('can:purchases.invoices.create');

    // Supplier Invoices
    Route::get('invoices', [SupplierInvoiceApiController::class, 'index'])->middleware('can:purchases.invoices.view');
    Route::post('invoices', [SupplierInvoiceApiController::class, 'store'])->middleware('can:purchases.invoices.create');
    Route::get('invoices/{id}', [SupplierInvoiceApiController::class, 'show'])->middleware('can:purchases.invoices.view');
    Route::put('invoices/{id}', [SupplierInvoiceApiController::class, 'update'])->middleware('can:purchases.invoices.edit');
    Route::delete('invoices/{id}', [SupplierInvoiceApiController::class, 'destroy'])->middleware('can:purchases.invoices.delete');

    // Supplier Payments
    Route::get('payments', [SupplierPaymentApiController::class, 'index'])->middleware('can:purchases.payments.view');
    Route::post('payments', [SupplierPaymentApiController::class, 'store'])->middleware('can:purchases.payments.create');
    Route::delete('payments/{id}', [SupplierPaymentApiController::class, 'destroy'])->middleware('can:purchases.payments.delete');

    // Purchase Returns
    Route::get('returns', [PurchaseReturnApiController::class, 'index'])->middleware('can:purchases.returns.view');
    Route::post('returns', [PurchaseReturnApiController::class, 'store'])->middleware('can:purchases.returns.create');
    Route::get('returns/{id}', [PurchaseReturnApiController::class, 'show'])->middleware('can:purchases.returns.view');
    Route::delete('returns/{id}', [PurchaseReturnApiController::class, 'destroy'])->middleware('can:purchases.returns.delete');
});
