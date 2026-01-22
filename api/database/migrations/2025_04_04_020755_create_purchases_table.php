<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->integer('due_days');
            $table->foreignId('warehouse_id')->references('id')->on('warehouses')->nullable();
            $table->foreignId('supplier_id')->references('id')->on('suppliers')->nullable();
            $table->foreignId('purchase_order_id')->references('id')->on('purchase_orders')->nullable();
            $table->string('purchase_tax_invoice_number');
            $table->decimal('purchase_tax_invoice_vat_base', 30, 8)->default(0);
            $table->decimal('purchase_tax_invoice_vat', 30, 8)->default(0);
            $table->string('return_tax_invoice_number');
            $table->decimal('return_tax_invoice_vat_base', 30, 8)->default(0);
            $table->decimal('return_tax_invoice_vat', 30, 8)->default(0);
            $table->string('remarks')->nullable();
            $table->boolean('is_posted')->default(false);
            $table->decimal('purchase_total', 30, 8)->default(0);
            $table->decimal('purchase_global_discount_rate', 30, 8)->default(0);
            $table->decimal('purchase_global_discount_fixed', 30, 8)->default(0);
            $table->decimal('purchase_additional_cost', 30, 8)->default(0);
            $table->decimal('purchase_rounding', 30, 8)->default(0);
            $table->decimal('return_total', 30, 8)->default(0);
            $table->decimal('return_global_discount_rate', 30, 8)->default(0);
            $table->decimal('return_global_discount_fixed', 30, 8)->default(0);
            $table->decimal('return_rounding', 30, 8)->default(0);
            $table->decimal('return_grand_total', 30, 8)->default(0);
            $table->decimal('amount_due', 30, 8)->default(0);
            $table->decimal('amount_paid_by_purchase_order_down_payment', 30, 8)->default(0);
            $table->decimal('amount_paid_by_purchase_return', 30, 8)->default(0);
            $table->decimal('amount_paid_before_invoice', 30, 8)->default(0);
            $table->decimal('amount_paid_on_invoice', 30, 8)->default(0);
            $table->decimal('amount_paid_after_invoice', 30, 8)->default(0);
            $table->decimal('amount_paid_total', 30, 8)->default(0);
            $table->boolean('is_paid_off')->default(false);
            $table->boolean('is_valid')->default(false);

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
