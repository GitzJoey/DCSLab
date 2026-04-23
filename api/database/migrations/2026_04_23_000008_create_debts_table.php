<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->foreignId('category_id')->references('id')->on('debt_categories');
            $table->foreignId('creditor_id')->nullable()->references('id')->on('debt_creditors');
            $table->foreignId('supplier_id')->nullable()->references('id')->on('suppliers');
            $table->foreignId('cash_account_id')->nullable()->references('id')->on('cash_accounts');
            $table->decimal('direct_amount_received', 30, 8)->default(0);
            $table->decimal('opening_amount_due', 30, 8)->default(0);
            $table->decimal('amount_total', 30, 8)->default(0);
            $table->decimal('amount_paid_by_cash_account', 30, 8)->default(0);
            $table->decimal('amount_paid_by_stock_adjustment', 30, 8)->default(0);
            $table->decimal('amount_due', 30, 8)->default(0);
            $table->integer('due_days')->default(0);
            $table->boolean('is_paid_off')->default(false);
            $table->string('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
