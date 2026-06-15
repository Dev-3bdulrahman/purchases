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
    Route::get('suppliers', [SupplierApiController::class, 'index'])->name('api.v1.purchases.suppliers.index');
    Route::post('suppliers', [SupplierApiController::class, 'store'])->name('api.v1.purchases.suppliers.store');
    Route::get('suppliers/{supplier}', [SupplierApiController::class, 'show'])->name('api.v1.purchases.suppliers.show');
    Route::put('suppliers/{supplier}', [SupplierApiController::class, 'update'])->name('api.v1.purchases.suppliers.update');
    Route::delete('suppliers/{supplier}', [SupplierApiController::class, 'destroy'])->name('api.v1.purchases.suppliers.destroy');

    // Purchase Requests
    Route::get('requests', [PurchaseRequestApiController::class, 'index'])->name('api.v1.purchases.requests.index');
    Route::post('requests', [PurchaseRequestApiController::class, 'store'])->name('api.v1.purchases.requests.store');
    Route::get('requests/{purchaseRequest}', [PurchaseRequestApiController::class, 'show'])->name('api.v1.purchases.requests.show');
    Route::put('requests/{purchaseRequest}', [PurchaseRequestApiController::class, 'update'])->name('api.v1.purchases.requests.update');
    Route::delete('requests/{purchaseRequest}', [PurchaseRequestApiController::class, 'destroy'])->name('api.v1.purchases.requests.destroy');
    Route::post('requests/{purchaseRequest}/convert', [PurchaseRequestApiController::class, 'convertToOrder'])->name('api.v1.purchases.requests.convert');

    // Purchase Orders
    Route::get('orders', [PurchaseOrderApiController::class, 'index'])->name('api.v1.purchases.orders.index');
    Route::post('orders', [PurchaseOrderApiController::class, 'store'])->name('api.v1.purchases.orders.store');
    Route::get('orders/{purchaseOrder}', [PurchaseOrderApiController::class, 'show'])->name('api.v1.purchases.orders.show');
    Route::put('orders/{purchaseOrder}', [PurchaseOrderApiController::class, 'update'])->name('api.v1.purchases.orders.update');
    Route::delete('orders/{purchaseOrder}', [PurchaseOrderApiController::class, 'destroy'])->name('api.v1.purchases.orders.destroy');
    Route::post('orders/{purchaseOrder}/convert-to-invoice', [PurchaseOrderApiController::class, 'convertToInvoice'])->name('api.v1.purchases.orders.convert-to-invoice');

    // Supplier Invoices
    Route::get('invoices', [SupplierInvoiceApiController::class, 'index'])->name('api.v1.purchases.invoices.index');
    Route::post('invoices', [SupplierInvoiceApiController::class, 'store'])->name('api.v1.purchases.invoices.store');
    Route::get('invoices/{supplierInvoice}', [SupplierInvoiceApiController::class, 'show'])->name('api.v1.purchases.invoices.show');
    Route::put('invoices/{supplierInvoice}', [SupplierInvoiceApiController::class, 'update'])->name('api.v1.purchases.invoices.update');
    Route::delete('invoices/{supplierInvoice}', [SupplierInvoiceApiController::class, 'destroy'])->name('api.v1.purchases.invoices.destroy');

    // Supplier Payments
    Route::get('payments', [SupplierPaymentApiController::class, 'index'])->name('api.v1.purchases.payments.index');
    Route::post('payments', [SupplierPaymentApiController::class, 'store'])->name('api.v1.purchases.payments.store');
    Route::delete('payments/{supplierPayment}', [SupplierPaymentApiController::class, 'destroy'])->name('api.v1.purchases.payments.destroy');

    // Purchase Returns
    Route::get('returns', [PurchaseReturnApiController::class, 'index'])->name('api.v1.purchases.returns.index');
    Route::post('returns', [PurchaseReturnApiController::class, 'store'])->name('api.v1.purchases.returns.store');
    Route::get('returns/{purchaseReturn}', [PurchaseReturnApiController::class, 'show'])->name('api.v1.purchases.returns.show');
    Route::put('returns/{purchaseReturn}', [PurchaseReturnApiController::class, 'update'])->name('api.v1.purchases.returns.update');
    Route::delete('returns/{purchaseReturn}', [PurchaseReturnApiController::class, 'destroy'])->name('api.v1.purchases.returns.destroy');
});
