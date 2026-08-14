<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            // Header
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->integer('due_days')->default(0);
            $table->foreignId('customer_id')->nullable()->references('id')->on('customers');

            // Header advanced
            $table->text('remarks')->nullable();

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
            $table->decimal('amount_allocated_down_payment', 30, 8)->default(0);
            $table->decimal('amount_refunded_down_payment', 30, 8)->default(0);
            $table->decimal('amount_available_down_payment', 30, 8)->default(0);
            $table->string('progress_status')->default('unlinked');
            $table->unsignedInteger('item_total_count')->default(0);
            $table->unsignedInteger('item_matched_count')->default(0);
            $table->unsignedInteger('item_less_count')->default(0);
            $table->unsignedInteger('item_more_count')->default(0);
            $table->unsignedInteger('item_unlinked_count')->default(0);

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
        Schema::dropIfExists('sales_orders');
    }
};
