<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_product_unit_serials', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('stock_transfer_id')->references('id')->on('stock_transfers');
            $table->foreignId('stock_transfer_product_unit_id');
            $table->foreign('stock_transfer_product_unit_id', 'fk_stpu_serials_stpu_id')->references('id')->on('stock_transfer_product_units');
            $table->string('serial');
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_product_unit_serials');
    }
};
