<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            // Header
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->foreignId('supplier_id')->references('id')->on('suppliers');
            $table->foreignId('purchase_id')->nullable()->references('id')->on('purchases');
            $table->boolean('is_from_direct_purchase')->default(false);
            $table->foreignId('warehouse_id')->references('id')->on('warehouses');
            $table->string('remarks')->nullable();
            $table->boolean('is_posted')->default(false);

            // Audit
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipts');
    }
};
