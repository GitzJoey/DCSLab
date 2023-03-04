<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('ulid')->nullable();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('supplier_id')->references('id')->on('suppliers');
            $table->string('invoice_code');
            $table->dateTime('invoice_date', $precision = 0);
            $table->dateTime('shipping_date', $precision = 0)->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('remarks')->nullable();
            $table->integer('status');
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
        Schema::dropIfExists('purchase_orders');
    }
}
