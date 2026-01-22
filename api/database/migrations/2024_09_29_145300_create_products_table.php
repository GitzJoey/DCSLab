<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->string('code');
            $table->foreignId('category_id')->references('id')->on('product_categories');
            $table->foreignId('brand_id')->nullable()->references('id')->on('brands');
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_taxable')->default(false);
            $table->decimal('vat_rate', 30, 8)->default(0);
            $table->boolean('is_price_include_vat')->default(false);
            $table->boolean('is_use_serial_number')->default(false);
            $table->boolean('is_expirable')->default(false);
            $table->string('remarks')->nullable();
            $table->integer('type');
            $table->integer('status');

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
