<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id');
            $table->foreign('company_id', 'fk_pori_company_id')->references('id')->on('companies');
            $table->foreignId('branch_id');
            $table->foreign('branch_id', 'fk_pori_branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_order_receipt_id');
            $table->foreign('purchase_order_receipt_id', 'fk_pori_purchase_order_receipt_id')->references('id')->on('purchase_order_receipts');
            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->comment('Only filled when the receipt item maps to a purchase order item (matched by product).');
            $table->foreign('purchase_order_item_id', 'fk_pori_purchase_order_item_id')->references('id')->on('purchase_order_items');
            $table->boolean('has_purchase_order_item_product')->default(false);
            $table->decimal('qty', 30, 8)->default(0);
            $table->foreignId('product_unit_id');
            $table->foreign('product_unit_id', 'fk_pori_product_unit_id')->references('id')->on('product_units');
            $table->foreignId('product_id');
            $table->foreign('product_id', 'fk_pori_product_id')->references('id')->on('products');
            $table->decimal('product_unit_conversion_value', 30, 8)->default(0);
            $table->decimal('product_unit_qty_base', 30, 8)->default(0);
            $table->decimal('base_unit_value', 30, 8)->default(0);
            $table->decimal('total_value', 30, 8)->default(0);
            $table->string('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_receipt_items');
    }
};
