<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderProductUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_product_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_order_id')->references('id')->on('purchase_orders');
            $table->foreignId('product_id')->references('id')->on('products');
            $table->foreignId('product_unit_id')->references('id')->on('product_units');
            $table->decimal('qty', 19, 8);
            $table->decimal('product_unit_amount_per_unit', 19, 8)->default(0);
            $table->decimal('product_unit_amount_total', 19, 8)->default(0);
            $table->decimal('product_unit_initial_price', 19, 8)->default(0);
            $table->decimal('product_unit_per_unit_discount', 19, 8)->default(0);
            $table->decimal('product_unit_sub_total', 19, 8)->default(0);
            $table->decimal('product_unit_per_unit_sub_total_discount', 19, 8)->default(0);
            $table->decimal('product_unit_total', 19, 8)->default(0);
            $table->decimal('product_unit_global_discount_percent', 19, 8)->default(0);
            $table->decimal('product_unit_global_discount_nominal', 19, 8)->default(0);
            $table->decimal('product_unit_final_price', 19, 8)->default(0);
            $table->integer('vat_status');
            $table->decimal('vat_rate', 19, 8);
            $table->decimal('vat_amount', 19, 8);
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_order_product_units');
    }
}
