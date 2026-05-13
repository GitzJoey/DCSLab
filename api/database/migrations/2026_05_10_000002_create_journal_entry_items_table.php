<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('journal_entry_id')->references('id')->on('journal_entries')->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')->references('id')->on('chart_of_accounts');
            $table->unsignedInteger('sequence')->default(0);
            $table->decimal('debit', 30, 8)->default(0);
            $table->decimal('credit', 30, 8)->default(0);
            $table->string('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->timestamps();

            $table->index(['company_id']);
            $table->index(['journal_entry_id', 'sequence']);
            $table->index(['chart_of_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_items');
    }
};
