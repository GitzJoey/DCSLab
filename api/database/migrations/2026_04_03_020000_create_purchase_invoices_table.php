<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            // Header
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->integer('due_days')->default(0);
            $table->foreignId('supplier_id')->references('id')->on('suppliers');
            $table->foreignId('purchase_order_id')->references('id')->on('purchase_orders');
            // Header advanced
            $table->string('tax_invoice_number')->nullable();
            $table->decimal('tax_invoice_vat_base', 30, 8)->default(0);
            $table->decimal('tax_invoice_vat', 30, 8)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('is_posted')->default(false);

            // Footer totals
            $table->decimal('item_total_before_global_discount', 30, 8)->default(0);
            $table->decimal('global_discount', 30, 8)->default(0);
            $table->decimal('item_total_after_global_discount', 30, 8)->default(0);
            $table->decimal('vat_base', 30, 8)->default(0);
            $table->decimal('vat', 30, 8)->default(0);
            $table->decimal('item_total_after_vat', 30, 8)->default(0);
            $table->decimal('rounding', 30, 8)->default(0);
            $table->decimal('amount_payable', 30, 8)->default(0);
            $table->decimal('amount_paid_down_payment', 30, 8)->default(0);
            $table->decimal('amount_paid_return', 30, 8)->default(0);
            $table->decimal('amount_paid_total', 30, 8)->default(0);
            $table->decimal('amount_due', 30, 8)->default(0);
            $table->boolean('is_paid_off')->default(false);

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
        Schema::dropIfExists('purchase_invoices');
    }
};
