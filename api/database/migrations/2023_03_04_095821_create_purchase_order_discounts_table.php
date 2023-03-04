<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_discounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_order_id')->nullable()->references('id')->on('purchase_orders');
            $table->foreignId('purchase_order_product_unit_id')->nullable()->references('id')->on('purchase_order_product_units');
            $table->string('discount_type');
            $table->decimal('amount', 19, 8)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_order_discounts');
    }
}
