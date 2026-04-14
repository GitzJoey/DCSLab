<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_return_id')->references('id')->on('purchase_returns');
            $table->foreignId('purchase_item_id')->nullable()->references('id')->on('purchase_items');
            $table->decimal('qty', 30, 8)->default(0);
            $table->foreignId('product_unit_id')->references('id')->on('product_units');
            $table->decimal('product_unit_conversion_value', 30, 8)->default(0);
            $table->decimal('product_unit_qty_base', 30, 8)->default(0);
            $table->decimal('product_unit_price', 30, 8)->default(0);
            $table->boolean('product_unit_is_price_include_vat')->default(false);
            $table->decimal('product_unit_price_discount', 30, 8)->default(0);
            $table->decimal('product_unit_price_after_discount', 30, 8)->default(0);
            $table->decimal('product_unit_subtotal', 30, 8)->default(0);
            $table->decimal('product_unit_subtotal_discount', 30, 8)->default(0);
            $table->decimal('product_unit_subtotal_after_discount', 30, 8)->default(0);
            $table->decimal('product_unit_global_discount', 30, 8)->default(0);
            $table->decimal('product_unit_total_before_vat', 30, 8)->default(0);

            $table->foreignId('vat_profile_id')->nullable()->references('id')->on('vat_profiles');
            $table->decimal('vat_rate', 30, 8)->default(0);
            $table->unsignedInteger('vat_base_numerator')->default(1);
            $table->unsignedInteger('vat_base_denominator')->default(1);
            $table->decimal('product_unit_vat_base', 30, 8)->default(0);
            $table->decimal('product_unit_vat', 30, 8)->default(0);
            $table->decimal('product_unit_additional_cost', 30, 8)->default(0);
            $table->decimal('product_unit_rounding', 30, 8)->default(0);
            $table->decimal('product_unit_grand_total', 30, 8)->default(0);
            $table->decimal('product_unit_cogs', 30, 8)->default(0);
            $table->decimal('product_unit_total_cogs', 30, 8)->default(0);
            $table->decimal('product_unit_base_unit_cogs', 30, 8)->default(0);
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
        Schema::dropIfExists('purchase_return_items');
    }
};
