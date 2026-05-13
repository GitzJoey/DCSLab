<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('asset_purchase_id')->references('id')->on('asset_purchases');
            $table->foreignId('asset_id')->references('id')->on('assets');
            $table->decimal('qty', 30, 8)->default(0);
            $table->decimal('unit_price', 30, 8)->default(0);
            $table->decimal('subtotal', 30, 8)->default(0);
            $table->decimal('allocated_additional_cost', 30, 8)->default(0);
            $table->decimal('subtotal_after_additional_cost', 30, 8)->default(0);
            $table->decimal('unit_acquisition_cost', 30, 8)->default(0);
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
        Schema::dropIfExists('asset_purchase_items');
    }
};
