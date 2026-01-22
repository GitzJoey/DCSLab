<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->date('date');
            $table->date('due_days');
            $table->foreignId('warehouse_id')->references('id')->on('warehouses');
            $table->foreignId('customer_id')->references('id')->on('customers');
            $table->foreignId('sales_order_id')->references('id')->on('sales_orders');

            $table->string('delivery_note_reference');

            $table->string('tax_invoice_number');
            $table->decimal('tax_invoice_vat_base', 30, 8)->default(0);
            $table->decimal('tax_invoice_vat', 30, 8)->default(0);
            $table->string('return_tax_invoice_number');
            $table->decimal('return_tax_invoice_vat_base', 30, 8)->default(0);
            $table->decimal('return_tax_invoice_vat', 30, 8)->default(0);

            $table->string('remarks')->nullable();
            $table->boolean('is_posted')->default(false);

            $table->decimal('total', 30, 8)->default(0);
            $table->decimal('global_discount_rate', 30, 8)->default(0);
            $table->decimal('global_discount_fixed', 30, 8)->default(0);
            $table->decimal('additional_cost', 30, 8)->default(0);
            $table->decimal('rounding', 30, 8)->default(0);
            $table->decimal('grand_total', 30, 8)->default(0);

            $table->decimal('return_total', 30, 8)->default(0);
            $table->decimal('return_global_discount_rate', 30, 8)->default(0);
            $table->decimal('return_global_discount_fixed', 30, 8)->default(0);
            $table->decimal('return_rounding', 30, 8)->default(0);
            $table->decimal('return_grand_total', 30, 8)->default(0);

            $table->decimal('amount_paid_by_sale_order_down_payment', 30, 8)->default(0);
            $table->decimal('amount_paid_by_sale_return', 30, 8)->default(0);
            $table->decimal('amount_paid_before_invoice', 30, 8)->default(0);
            $table->decimal('amount_paid_on_invoice', 30, 8)->default(0);
            $table->decimal('amount_paid_after_invoice', 30, 8)->default(0);
            $table->decimal('amount_paid_total', 30, 8)->default(0);
            $table->decimal('amount_due', 30, 8)->default(0);

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
        Schema::dropIfExists('sales');
    }
};
