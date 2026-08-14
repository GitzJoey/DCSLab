<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            // Header
            $table->foreignId('company_id');
            $table->foreign('company_id', 'fk_sip_company_id')->references('id')->on('companies');
            $table->foreignId('branch_id');
            $table->foreign('branch_id', 'fk_sip_branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->foreignId('sales_invoice_id');
            $table->foreign('sales_invoice_id', 'fk_sip_sales_invoice_id')->references('id')->on('sales_invoices');
            $table->string('payment_type');
            $table->foreignId('cash_account_id')->nullable();
            $table->foreign('cash_account_id', 'fk_sip_cash_account_id')->references('id')->on('cash_accounts');
            $table->foreignId('sales_order_payment_id')->nullable();
            $table->foreign('sales_order_payment_id', 'fk_sip_sales_order_payment_id')->references('id')->on('sales_order_payments');
            $table->foreignId('sales_return_id')->nullable();
            $table->foreign('sales_return_id', 'fk_sip_sales_return_id')->references('id')->on('sales_returns');
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
        Schema::dropIfExists('sales_invoice_payments');
    }
};
