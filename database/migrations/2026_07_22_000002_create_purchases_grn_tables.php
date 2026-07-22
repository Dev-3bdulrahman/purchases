<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases_goods_receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('purchase_order_id')->constrained('purchases_orders')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('purchases_suppliers')->onDelete('cascade');
            $table->string('grn_number');
            $table->date('receipt_date');
            $table->string('status')->default('draft'); // draft, partial, completed, cancelled
            $table->text('notes')->nullable();
            $table->string('received_by')->nullable();
            $table->boolean('quality_check_passed')->default(false);
            $table->text('quality_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('purchase_order_id');
            $table->index('grn_number');
            $table->index('status');
        });

        Schema::create('purchases_grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_note_id')->constrained('purchases_goods_receipt_notes')->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->constrained('purchases_order_items')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('ordered_quantity', 12, 4)->default(0.0000);
            $table->decimal('received_quantity', 12, 4)->default(0.0000);
            $table->decimal('accepted_quantity', 12, 4)->default(0.0000);
            $table->decimal('rejected_quantity', 12, 4)->default(0.0000);
            $table->string('rejection_reason')->nullable();
            $table->string('batch_id')->nullable();
            $table->string('serial_number_id')->nullable();
            $table->decimal('unit_price', 15, 4)->default(0.0000);
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->timestamps();

            $table->index('goods_receipt_note_id');
            $table->index('purchase_order_item_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases_grn_items');
        Schema::dropIfExists('purchases_goods_receipt_notes');
    }
};
