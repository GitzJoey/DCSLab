<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_receipt_id')->references('id')->on('purchase_receipts');
            $table->foreignId('purchase_item_id')->nullable()->references('id')->on('purchase_items');
            $table->decimal('qty', 30, 8)->default(0);
            $table->foreignId('product_unit_id')->references('id')->on('product_units');
            $table->decimal('product_unit_conversion_value', 30, 8)->default(0);
            $table->decimal('product_unit_qty_base', 30, 8)->default(0);
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
        Schema::dropIfExists('purchase_receipt_items');
    }
};
