<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->ulid();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('product_group_id')->references('id')->on('product_groups');
            $table->foreignId('brand_id')->nullable()->references('id')->on('brands');
            $table->string('code');
            $table->string('name');
            $table->integer('product_type');
            $table->boolean('taxable_supply')->default(false);
            $table->integer('standard_rated_supply')->default(0);
            $table->boolean('price_include_vat')->default(false);
            $table->integer('point')->default(0);
            $table->boolean('use_serial_number')->default(false);
            $table->boolean('has_expiry_date')->default(false);
            $table->integer('status');
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'code',
                'name'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
