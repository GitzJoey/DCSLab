<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('product_id')->references('id')->on('products');
            $table->string('code');
            $table->boolean('is_manufacturer_sku')->default(false);
            $table->foreignId('unit_id')->references('id')->on('units');
            $table->decimal('price', 30, 8)->default(0);
            $table->boolean('is_base')->default(false);
            $table->decimal('conversion_value', 30, 8)->default(0);
            $table->boolean('is_primary_unit')->default(false);
            $table->integer('point')->default(0);
            $table->string('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
