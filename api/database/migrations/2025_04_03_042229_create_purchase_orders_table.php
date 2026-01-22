<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('supplier_id')->references('id')->on('suppliers');
            $table->string('code');
            $table->dateTime('date');
            $table->dateTime('shipping_date')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('is_has_invoice')->default(0);
            $table->boolean('is_received')->default(0);
            $table->decimal('total', 30, 8)->default(0);
            $table->decimal('global_discount_rate', 30, 8)->default(0);
            $table->decimal('global_discount_fixed', 30, 8)->default(0);
            $table->decimal('grand_total', 30, 8)->default(0);
            $table->decimal('down_payment', 30, 8)->default(0);
            $table->integer('down_payment_due_days')->default(0);
            $table->decimal('down_payment_applied', 30, 8)->default(0);
            $table->decimal('down_payment_remaining', 30, 8)->default(0);
            $table->boolean('is_down_payment_paid_off')->default(false);

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
