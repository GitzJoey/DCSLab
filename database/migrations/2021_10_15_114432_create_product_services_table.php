<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('group_id')->references('id')->on('product_service_groups')->onUpdate('cascade')->onDelete('restrict');;
            $table->foreignId('unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('restrict');;
            $table->string('code')->nullable();
            $table->string('name')->nullable();	
			$table->decimal('price', 19, 8)->default(0);
			$table->integer('tax_status')->nullable();
            $table->string('remarks')->nullable();	
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
        Schema::dropIfExists('product_services');
    }
}
