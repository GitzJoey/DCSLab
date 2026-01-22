<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_receipt_product_units', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('sale_receipt_id')->references('id')->on('sale_receipts');
            $table->string('code');
            $table->decimal('qty', 30, 8)->default(0);
            $table->foreignId('product_id')->references('id')->on('products');
            $table->foreignId('product_unit_id')->references('id')->on('product_units');
            $table->decimal('product_unit_amount_per_unit', 30, 8)->default(0);
            $table->decimal('product_unit_amount_total', 30, 8)->default(0);
            $table->boolean('is_has_sale')->default(false);

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_receipt_product_units');
    }
};
