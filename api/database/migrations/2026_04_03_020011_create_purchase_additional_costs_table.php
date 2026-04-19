<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_additional_costs', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_id')->references('id')->on('purchases');
            $table->foreignId('purchase_additional_cost_category_id')->references('id')->on('purchase_additional_cost_categories');

            $table->string('code');
            $table->dateTime('date');
            $table->integer('due_days')->default(0);
            $table->foreignId('paid_immediately_cash_account_id')->nullable()->references('id')->on('cash_accounts');
            $table->decimal('amount_paid_immediately', 30, 8)->default(0);
            $table->decimal('amount_payable', 30, 8)->default(0);
            $table->decimal('amount_payable_paid', 30, 8)->default(0);
            $table->decimal('amount_payable_due', 30, 8)->default(0);
            $table->boolean('is_amount_payable_paid_off')->default(false);
            $table->decimal('amount_total', 30, 8)->default(0);
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
        Schema::dropIfExists('purchase_additional_costs');
    }
};
