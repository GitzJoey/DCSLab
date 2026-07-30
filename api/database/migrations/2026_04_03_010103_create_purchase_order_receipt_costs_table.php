<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_receipt_costs', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            // Header
            $table->foreignId('company_id');
            $table->foreign('company_id', 'fk_porc_company_id')->references('id')->on('companies');
            $table->foreignId('branch_id');
            $table->foreign('branch_id', 'fk_porc_branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_order_receipt_id');
            $table->foreign('purchase_order_receipt_id', 'fk_porc_purchase_order_receipt_id')->references('id')->on('purchase_order_receipts');
            $table->string('code');
            $table->dateTime('date');
            $table->string('name');
            $table->foreignId('cash_account_id');
            $table->foreign('cash_account_id', 'fk_porc_cash_account_id')->references('id')->on('cash_accounts');
            $table->decimal('amount', 30, 8)->default(0);

            // Header advanced
            $table->string('remarks')->nullable();

            // Audit
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_receipt_costs');
    }
};
