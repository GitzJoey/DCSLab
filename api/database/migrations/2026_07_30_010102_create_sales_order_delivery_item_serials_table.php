<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_delivery_item_serials', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id');
            $table->foreign('company_id', 'fk_sodis_company_id')->references('id')->on('companies');
            $table->foreignId('branch_id');
            $table->foreign('branch_id', 'fk_sodis_branch_id')->references('id')->on('branches');
            $table->foreignId('sales_order_delivery_id');
            $table->foreign('sales_order_delivery_id', 'fk_sodis_sales_order_delivery_id')->references('id')->on('sales_order_deliveries');
            $table->foreignId('sales_order_delivery_item_id');
            $table->foreign('sales_order_delivery_item_id', 'fk_sodis_sales_order_delivery_item_id')->references('id')->on('sales_order_delivery_items');
            $table->string('serial');

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_delivery_item_serials');
    }
};
