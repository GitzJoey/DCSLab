<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('code')->nullable();
			$table->integer('group_id')->nullable();
			$table->integer('brand_id')->nullable();
			$table->string('name')->nullable();	
			$table->integer('unit_id')->nullable();
			$table->decimal('price', 19, 8)->default(0);
			$table->integer('tax')->nullable();
            $table->string('information')->nullable();	
			$table->decimal('estimated_capital_price', 19, 8)->default(0);
			$table->integer('point')->nullable();
			$table->integer('is_use_serial')->nullable();
			$table->integer('is_buy')->nullable();
			$table->integer('is_production_materials')->nullable();
			$table->integer('is_production_result')->nullable();
			$table->integer('is_sell')->nullable();
			$table->integer('is_active')->nullable();
			$table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::drop('products');
    }
}
