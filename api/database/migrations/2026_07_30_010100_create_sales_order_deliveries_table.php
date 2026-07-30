<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            // Header
            $table->foreignId('company_id');
            $table->foreign('company_id', 'fk_sod_company_id')->references('id')->on('companies');
            $table->foreignId('branch_id');
            $table->foreign('branch_id', 'fk_sod_branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->foreignId('customer_id');
            $table->foreign('customer_id', 'fk_sod_customer_id')->references('id')->on('customers');
            $table->foreignId('sales_order_id');
            $table->foreign('sales_order_id', 'fk_sod_sales_order_id')->references('id')->on('sales_orders');
            $table->foreignId('warehouse_id');
            $table->foreign('warehouse_id', 'fk_sod_warehouse_id')->references('id')->on('warehouses');
            $table->string('remarks')->nullable();
            $table->boolean('is_posted')->default(false);

            // Footer totals
            $table->decimal('total_cogs', 30, 8)->default(0);
            $table->decimal('total_cost', 30, 8)->default(0);

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
        Schema::dropIfExists('sales_order_deliveries');
    }
};
