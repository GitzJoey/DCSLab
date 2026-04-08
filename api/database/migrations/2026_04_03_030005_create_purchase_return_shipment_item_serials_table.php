<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_shipment_item_serials', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_return_shipment_id');
            $table->foreign('purchase_return_shipment_id', 'fk_prsis_prs_id')->references('id')->on('purchase_return_shipments');
            $table->foreignId('purchase_return_shipment_item_id');
            $table->foreign('purchase_return_shipment_item_id', 'fk_prsis_prsi_id')->references('id')->on('purchase_return_shipment_items');
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
        Schema::dropIfExists('purchase_return_shipment_item_serials');
    }
};
