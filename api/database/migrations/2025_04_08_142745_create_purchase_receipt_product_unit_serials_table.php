<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipt_product_unit_serials', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');

            $table->foreignId('purchase_receipt_id');
            $table->foreign('purchase_receipt_id', 'fk_p_receipt_serials_pr_id')->references('id')->on('purchase_receipts');

            $table->foreignId('purchase_receipt_product_unit_id');
            $table->foreign('purchase_receipt_product_unit_id', 'fk_p_receipt_pu_serials_prpu_id')->references('id')->on('purchase_receipt_product_units');

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
        Schema::dropIfExists('purchase_receipt_product_unit_serials');
    }
};
