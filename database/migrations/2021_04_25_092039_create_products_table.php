<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('code')->nullable();
			$table->foreignId('group_id')->references('id')->on('product_groups')->onUpdate('cascade')->onDelete('restrict');;
            $table->foreignId('brand_id')->references('id')->on('product_brands')->onUpdate('cascade')->onDelete('restrict');;
            $table->string('name')->nullable();	
            $table->foreignId('unit_id')->references('id')->on('product_units')->onUpdate('cascade')->onDelete('restrict');;
			$table->decimal('price', 19, 8)->default(0);
			$table->integer('tax_status')->nullable();
            $table->string('information')->nullable();	
			$table->decimal('estimated_capital_price', 19, 8)->default(0);
			$table->integer('point')->nullable();
			$table->integer('is_use_serial')->nullable();
			$table->integer('is_buy')->nullable();
			$table->integer('is_production_materials')->nullable();
			$table->integer('is_production_result')->nullable();
			$table->integer('is_sell')->nullable();
			$table->integer('active_status')->nullable();
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
        Schema::dropIfExists('products');
    }
}
