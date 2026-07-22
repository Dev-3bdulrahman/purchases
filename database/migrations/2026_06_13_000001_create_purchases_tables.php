<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Purchases Suppliers (الموردون)
        Schema::create('purchases_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('status');
        });

        // 2. Purchase Requests (طلبات الشراء)
        Schema::create('purchases_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('request_number');
            $table->date('request_date');
            $table->string('status')->default('draft'); // draft, pending, approved, rejected
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('request_number');
            $table->index('status');
        });

        // 3. Purchase Request Items (عناصر طلب الشراء)
        Schema::create('purchases_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchases_requests')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4)->nullable();
            $table->decimal('total', 15, 4)->nullable();
            $table->timestamps();

            $table->index('purchase_request_id');
            $table->index('product_id');
        });

        // 4. Purchase Orders (أوامر الشراء)
        Schema::create('purchases_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('supplier_id')->constrained('purchases_suppliers')->onDelete('cascade');
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchases_requests')->onDelete('set null');
            $table->string('order_number');
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->string('status')->default('draft'); // draft, pending, confirmed, received, cancelled
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('tax_total', 15, 4)->default(0.0000);
            $table->decimal('discount_total', 15, 4)->default(0.0000);
            $table->decimal('grand_total', 15, 4)->default(0.0000);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('supplier_id');
            $table->index('order_number');
            $table->index('status');
        });

        // 5. Purchase Order Items (عناصر أمر الشراء)
        Schema::create('purchases_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchases_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('discount_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4);
            $table->timestamps();

            $table->index('purchase_order_id');
            $table->index('product_id');
        });

        // 6. Supplier Invoices (فواتير الموردين)
        Schema::create('purchases_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('supplier_id')->constrained('purchases_suppliers')->onDelete('cascade');
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchases_orders')->onDelete('set null');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('status')->default('draft'); // draft, unpaid, partially_paid, paid, overdue, cancelled
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('tax_total', 15, 4)->default(0.0000);
            $table->decimal('discount_total', 15, 4)->default(0.0000);
            $table->decimal('grand_total', 15, 4)->default(0.0000);
            $table->decimal('paid_amount', 15, 4)->default(0.0000);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('supplier_id');
            $table->index('invoice_number');
            $table->index('status');
        });

        // 7. Supplier Invoice Items (عناصر فاتورة المورد)
        Schema::create('purchases_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained('purchases_invoices')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('discount_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4);
            $table->timestamps();

            $table->index('supplier_invoice_id', 'pur_inv_items_invoice_id_idx');
            $table->index('product_id');
        });

        // 8. Supplier Payments (مدفوعات الموردين)
        Schema::create('purchases_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('supplier_invoice_id')->constrained('purchases_invoices')->onDelete('cascade');
            $table->string('payment_number');
            $table->date('payment_date');
            $table->string('payment_method')->default('cash'); // cash, bank_transfer, card, check, online
            $table->decimal('amount', 15, 4);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('supplier_invoice_id');
            $table->index('payment_number');
        });

        // 9. Purchase Returns (مرتجع المشتريات)
        Schema::create('purchases_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('supplier_id')->constrained('purchases_suppliers')->onDelete('cascade');
            $table->foreignId('supplier_invoice_id')->nullable()->constrained('purchases_invoices')->onDelete('set null');
            $table->string('return_number');
            $table->date('return_date');
            $table->string('status')->default('pending'); // pending, approved, rejected, completed
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('tax_total', 15, 4)->default(0.0000);
            $table->decimal('discount_total', 15, 4)->default(0.0000);
            $table->decimal('grand_total', 15, 4)->default(0.0000);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('supplier_id');
            $table->index('return_number');
            $table->index('status');
        });

        // 10. Purchase Return Items (عناصر مرتجع المشتريات)
        Schema::create('purchases_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained('purchases_returns')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('discount_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4);
            $table->timestamps();

            $table->index('purchase_return_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases_return_items');
        Schema::dropIfExists('purchases_returns');
        Schema::dropIfExists('purchases_payments');
        Schema::dropIfExists('purchases_invoice_items');
        Schema::dropIfExists('purchases_invoices');
        Schema::dropIfExists('purchases_order_items');
        Schema::dropIfExists('purchases_orders');
        Schema::dropIfExists('purchases_request_items');
        Schema::dropIfExists('purchases_requests');
        Schema::dropIfExists('purchases_suppliers');
    }
};
