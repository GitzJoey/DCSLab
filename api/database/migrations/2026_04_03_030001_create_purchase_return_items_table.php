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

            $table->foreignId('company_id');
            $table->foreign('company_id', 'fk_prti_company_id')->references('id')->on('companies');
            $table->foreignId('branch_id');
            $table->foreign('branch_id', 'fk_prti_branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_return_id');
            $table->foreign('purchase_return_id', 'fk_prti_purchase_return_id')->references('id')->on('purchase_returns');
            $table->foreignId('purchase_order_receipt_item_id')->nullable();
            $table->foreign('purchase_order_receipt_item_id', 'fk_prti_purchase_order_receipt_item_id')->references('id')->on('purchase_order_receipt_items');
            $table->decimal('qty', 30, 8)->default(0);
            $table->foreignId('product_unit_id');
            $table->foreign('product_unit_id', 'fk_prti_product_unit_id')->references('id')->on('product_units');
            $table->foreignId('product_id');
            $table->foreign('product_id', 'fk_prti_product_id')->references('id')->on('products');
            $table->decimal('product_unit_conversion_value', 30, 8)->default(0);
            $table->decimal('product_unit_qty_base', 30, 8)->default(0);
            $table->decimal('product_unit_price', 30, 8)->default(0);
            $table->boolean('product_unit_is_price_include_vat')->default(false);
            $table->decimal('price_discount', 30, 8)->default(0);
            $table->decimal('price_after_discount', 30, 8)->default(0);
            $table->decimal('subtotal', 30, 8)->default(0);
            $table->decimal('subtotal_discount', 30, 8)->default(0);
            $table->decimal('subtotal_after_discount', 30, 8)->default(0);
            $table->decimal('global_discount', 30, 8)->default(0);
            $table->decimal('subtotal_after_global_discount', 30, 8)->default(0);

            $table->foreignId('vat_profile_id')->nullable();
            $table->foreign('vat_profile_id', 'fk_prti_vat_profile_id')->references('id')->on('vat_profiles');
            $table->decimal('vat_rate', 30, 8)->default(0);
            $table->unsignedInteger('vat_base_numerator')->default(1);
            $table->unsignedInteger('vat_base_denominator')->default(1);
            $table->decimal('vat_base', 30, 8)->default(0);
            $table->decimal('vat', 30, 8)->default(0);
            $table->decimal('subtotal_after_vat', 30, 8)->default(0);
            $table->decimal('rounding', 30, 8)->default(0);
            $table->decimal('amount_payable', 30, 8)->default(0);
            $table->decimal('cogs', 30, 8)->default(0);
            $table->decimal('total_cogs', 30, 8)->default(0);
            $table->decimal('base_unit_cogs', 30, 8)->default(0);
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
