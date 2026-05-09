<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->string('scope')->default('user');
            $table->string('system_key')->nullable();
            $table->foreignId('parent_id')->nullable()->references('id')->on('chart_of_accounts');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('code');
            $table->string('name');
            $table->string('account_type');
            $table->string('normal_balance');
            $table->unsignedInteger('level')->default(1);
            $table->boolean('is_group')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'system_key']);

            $table->index(['company_id', 'scope']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
