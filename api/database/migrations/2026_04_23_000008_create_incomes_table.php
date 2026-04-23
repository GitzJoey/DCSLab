<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->foreignId('income_category_id')->references('id')->on('income_categories');
            $table->foreignId('paid_immediately_cash_account_id')->nullable();
            $table->foreign('paid_immediately_cash_account_id', 'ins_pica_id_fk')->references('id')->on('cash_accounts');
            $table->decimal('amount_paid_immediately', 30, 8)->default(0);
            $table->decimal('amount_receivable', 30, 8)->default(0);
            $table->integer('due_days')->default(0);
            $table->decimal('amount_receivable_paid', 30, 8)->default(0);
            $table->decimal('amount_receivable_due', 30, 8)->default(0);
            $table->boolean('is_amount_receivable_paid_off')->default(false);
            $table->decimal('amount_total', 30, 8)->default(0);
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
        Schema::dropIfExists('incomes');
    }
};
