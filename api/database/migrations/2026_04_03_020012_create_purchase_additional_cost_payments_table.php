<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_additional_cost_payments', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            // Header
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->foreignId('purchase_additional_cost_id');
            $table->foreign('purchase_additional_cost_id', 'pacps_pac_id_fk')->references('id')->on('purchase_additional_costs');
            $table->foreignId('cash_account_id');
            $table->foreign('cash_account_id', 'pacps_ca_id_fk')->references('id')->on('cash_accounts');
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
        Schema::dropIfExists('purchase_additional_cost_payments');
    }
};
