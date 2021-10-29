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
            $table->foreignId('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('group_id')->references('id')->on('product_groups')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('brand_id')->references('id')->on('product_brands')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('supplier_id')->references('id')->on('suppliers')->onUpdate('cascade')->onDelete('restrict');
            $table->string('code')->nullable();
            $table->string('name')->nullable();	
			$table->integer('tax_status')->nullable();
            $table->string('remarks')->nullable();	
			$table->integer('point')->nullable();
			$table->integer('is_use_serial')->nullable();
            $table->integer('product_type')->nullable(); //[RAW, WIP, FINISHED GOOD]
            $table->integer('is_service')->default(0);            
            $table->integer('status')->nullable();
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
