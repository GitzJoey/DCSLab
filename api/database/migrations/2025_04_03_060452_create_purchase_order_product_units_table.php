<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_product_units', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_order_id')->references('id')->on('purchase_orders');
            $table->integer('qty');
            $table->foreignId('product_id')->references('id')->on('products');
            $table->foreignId('product_unit_id')->references('id')->on('product_units');
            $table->decimal('product_unit_amount_per_unit', 30, 8)->default(0);
            $table->decimal('product_unit_amount_total', 30, 8)->default(0);
            $table->decimal('product_unit_initial_price', 30, 8)->default(0);
            $table->decimal('product_unit_discount_rate1', 30, 8)->default(0);
            $table->decimal('product_unit_discount_rate2', 30, 8)->default(0);
            $table->decimal('product_unit_discount_rate3', 30, 8)->default(0);
            $table->decimal('product_unit_discount_rate4', 30, 8)->default(0);
            $table->decimal('product_unit_discount_rate5', 30, 8)->default(0);
            $table->decimal('product_unit_discount_fixed1', 30, 8)->default(0);
            $table->decimal('product_unit_discount_fixed2', 30, 8)->default(0);
            $table->decimal('product_unit_discount_fixed3', 30, 8)->default(0);
            $table->decimal('product_unit_discount_fixed4', 30, 8)->default(0);
            $table->decimal('product_unit_discount_fixed5', 30, 8)->default(0);
            $table->decimal('product_unit_net_price', 30, 8)->default(0);
            $table->decimal('product_unit_subtotal', 30, 8)->default(0);
            $table->decimal('product_unit_subtotal_discount_rate', 30, 8)->default(0);
            $table->decimal('product_unit_subtotal_discount_fixed', 30, 8)->default(0);
            $table->decimal('product_unit_total', 30, 8)->default(0);
            $table->decimal('product_unit_global_discount_rate', 30, 8)->default(0);
            $table->decimal('product_unit_global_discount_fixed', 30, 8)->default(0);
            $table->decimal('product_unit_grand_total', 30, 8)->default(0);
            $table->boolean('product_is_taxable')->default(false);
            $table->decimal('product_vat_rate', 30, 8)->default(0);
            $table->boolean('product_is_price_include_vat')->default(false);
            $table->decimal('product_vat_base', 30, 8)->default(0);
            $table->decimal('product_vat', 30, 8)->default(0);
            $table->decimal('product_unit_final_price', 30, 8)->default(0);
            $table->decimal('product_final_price_base_unit', 30, 8)->default(0);
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
        Schema::dropIfExists('purchase_order_product_units');
    }
};
