<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_adjustment_out_item_serials', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('asset_adjustment_id')->references('id')->on('asset_adjustments');

            $table->foreignId('asset_adjustment_out_item_id');
            $table->foreign('asset_adjustment_out_item_id', 'fk_aaop_serials_aaop_id')->references('id')->on('asset_adjustment_out_items');

            $table->string('serial');

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['asset_adjustment_out_item_id', 'serial'], 'uq_aaop_serials_item_serial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_adjustment_out_item_serials');
    }
};
