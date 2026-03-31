<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_product_unit_discounts', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_order_product_unit_id');
            $table->foreign('purchase_order_product_unit_id', 'fk_popu_discounts_popu_id')->references('id')->on('purchase_order_product_units');
            $table->unsignedInteger('sequence');
            $table->decimal('rate', 30, 8)->default(0);
            $table->decimal('fixed', 30, 8)->default(0);

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['purchase_order_product_unit_id', 'sequence'], 'po_product_unit_discount_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_product_unit_discounts');
    }
};
