<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_sale_item_serials', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('asset_sale_id')->references('id')->on('asset_sales');
            $table->foreignId('asset_sale_item_id')->references('id')->on('asset_sale_items');
            $table->foreignId('asset_serial_id')->references('id')->on('asset_serials');
            $table->string('serial');

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['asset_sale_item_id', 'asset_serial_id'], 'uq_asis_item_asset_serial');
            $table->unique(['asset_sale_item_id', 'serial'], 'uq_asis_item_serial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_sale_item_serials');
    }
};
