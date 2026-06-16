<?php

use Illuminate\Support\Facades\Route;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\SupplierApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\PurchaseRequestApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\PurchaseOrderApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\SupplierInvoiceApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\SupplierPaymentApiController;
use Dev3bdulrahman\Purchases\Http\Controllers\Api\PurchaseReturnApiController;

Route::prefix('api/v1/purchases')->middleware(['auth:sanctum', 'throttle:60,1', 'api.tenant'])->group(function () {
    // Suppliers
    Route::get('suppliers', [SupplierApiController::class, 'index'])->middleware('can:purchases.suppliers.view')->name('api.v1.purchases.suppliers.index');
    Route::post('suppliers', [SupplierApiController::class, 'store'])->middleware('can:purchases.suppliers.create')->name('api.v1.purchases.suppliers.store');
    Route::get('suppliers/{supplier}', [SupplierApiController::class, 'show'])->middleware('can:purchases.suppliers.view')->name('api.v1.purchases.suppliers.show');
    Route::put('suppliers/{supplier}', [SupplierApiController::class, 'update'])->middleware('can:purchases.suppliers.edit')->name('api.v1.purchases.suppliers.update');
    Route::delete('suppliers/{supplier}', [SupplierApiController::class, 'destroy'])->middleware('can:purchases.suppliers.delete')->name('api.v1.purchases.suppliers.destroy');

    // Purchase Requests
    Route::get('requests', [PurchaseRequestApiController::class, 'index'])->middleware('can:purchases.requests.view')->name('api.v1.purchases.requests.index');
    Route::post('requests', [PurchaseRequestApiController::class, 'store'])->middleware('can:purchases.requests.create')->name('api.v1.purchases.requests.store');
    Route::get('requests/{purchaseRequest}', [PurchaseRequestApiController::class, 'show'])->middleware('can:purchases.requests.view')->name('api.v1.purchases.requests.show');
    Route::put('requests/{purchaseRequest}', [PurchaseRequestApiController::class, 'update'])->middleware('can:purchases.requests.edit')->name('api.v1.purchases.requests.update');
    Route::delete('requests/{purchaseRequest}', [PurchaseRequestApiController::class, 'destroy'])->middleware('can:purchases.requests.delete')->name('api.v1.purchases.requests.destroy');
    Route::post('requests/{purchaseRequest}/convert', [PurchaseRequestApiController::class, 'convertToOrder'])->middleware('can:purchases.requests.convert')->name('api.v1.purchases.requests.convert');

    // Purchase Orders
    Route::get('orders', [PurchaseOrderApiController::class, 'index'])->middleware('can:purchases.orders.view')->name('api.v1.purchases.orders.index');
    Route::post('orders', [PurchaseOrderApiController::class, 'store'])->middleware('can:purchases.orders.create')->name('api.v1.purchases.orders.store');
    Route::get('orders/{purchaseOrder}', [PurchaseOrderApiController::class, 'show'])->middleware('can:purchases.orders.view')->name('api.v1.purchases.orders.show');
    Route::put('orders/{purchaseOrder}', [PurchaseOrderApiController::class, 'update'])->middleware('can:purchases.orders.edit')->name('api.v1.purchases.orders.update');
    Route::delete('orders/{purchaseOrder}', [PurchaseOrderApiController::class, 'destroy'])->middleware('can:purchases.orders.delete')->name('api.v1.purchases.orders.destroy');
    Route::post('orders/{purchaseOrder}/convert-to-invoice', [PurchaseOrderApiController::class, 'convertToInvoice'])->middleware('can:purchases.orders.convert')->name('api.v1.purchases.orders.convert-to-invoice');

    // Supplier Invoices
    Route::get('invoices', [SupplierInvoiceApiController::class, 'index'])->middleware('can:purchases.invoices.view')->name('api.v1.purchases.invoices.index');
    Route::post('invoices', [SupplierInvoiceApiController::class, 'store'])->middleware('can:purchases.invoices.create')->name('api.v1.purchases.invoices.store');
    Route::get('invoices/{supplierInvoice}', [SupplierInvoiceApiController::class, 'show'])->middleware('can:purchases.invoices.view')->name('api.v1.purchases.invoices.show');
    Route::put('invoices/{supplierInvoice}', [SupplierInvoiceApiController::class, 'update'])->middleware('can:purchases.invoices.edit')->name('api.v1.purchases.invoices.update');
    Route::delete('invoices/{supplierInvoice}', [SupplierInvoiceApiController::class, 'destroy'])->middleware('can:purchases.invoices.delete')->name('api.v1.purchases.invoices.destroy');

    // Supplier Payments
    Route::get('payments', [SupplierPaymentApiController::class, 'index'])->middleware('can:purchases.payments.view')->name('api.v1.purchases.payments.index');
    Route::post('payments', [SupplierPaymentApiController::class, 'store'])->middleware('can:purchases.payments.create')->name('api.v1.purchases.payments.store');
    Route::delete('payments/{supplierPayment}', [SupplierPaymentApiController::class, 'destroy'])->middleware('can:purchases.payments.delete')->name('api.v1.purchases.payments.destroy');

    // Purchase Returns
    Route::get('returns', [PurchaseReturnApiController::class, 'index'])->middleware('can:purchases.returns.view')->name('api.v1.purchases.returns.index');
    Route::post('returns', [PurchaseReturnApiController::class, 'store'])->middleware('can:purchases.returns.create')->name('api.v1.purchases.returns.store');
    Route::get('returns/{purchaseReturn}', [PurchaseReturnApiController::class, 'show'])->middleware('can:purchases.returns.view')->name('api.v1.purchases.returns.show');
    Route::put('returns/{purchaseReturn}', [PurchaseReturnApiController::class, 'update'])->middleware('can:purchases.returns.edit')->name('api.v1.purchases.returns.update');
    Route::delete('returns/{purchaseReturn}', [PurchaseReturnApiController::class, 'destroy'])->middleware('can:purchases.returns.delete')->name('api.v1.purchases.returns.destroy');
});
