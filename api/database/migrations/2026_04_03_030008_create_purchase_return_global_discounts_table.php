<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_global_discounts', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id');
            $table->foreign('company_id', 'fk_prgd_company_id')->references('id')->on('companies');
            $table->foreignId('branch_id');
            $table->foreign('branch_id', 'fk_prgd_branch_id')->references('id')->on('branches');
            $table->foreignId('purchase_return_id');
            $table->foreign('purchase_return_id', 'fk_prgd_pr_id')->references('id')->on('purchase_returns');
            $table->integer('sequence')->default(0);
            $table->string('discount_type');
            $table->decimal('discount_value', 30, 8)->default(0);

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_global_discounts');
    }
};
