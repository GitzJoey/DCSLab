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
            $table->id();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('product_group_id')->nullable()->references('id')->on('product_groups');
            $table->foreignId('brand_id')->references('id')->on('brands');
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->integer('product_type')->nullable();
            $table->boolean('taxable_supply')->default(false);
            $table->integer('standard_rated_supply')->default(0);
            $table->boolean('price_include_vat')->default(false);
            $table->integer('point')->nullable();
            $table->boolean('use_serial_number')->default(false);
            $table->boolean('has_expiry_date')->default(false);
            $table->integer('status')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'created_at']);
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
