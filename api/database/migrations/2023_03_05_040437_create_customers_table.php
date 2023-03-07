<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->ulid();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('customer_group_id')->references('id')->on('customer_groups');
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->string('code');
            $table->integer('is_member');
            $table->string('name');
            $table->string('zone')->nullable();
            $table->integer('max_open_invoice')->default(0);
            $table->decimal('max_outstanding_invoice', $precision = 16, $scale = 8)->default(0);
            $table->integer('max_invoice_age')->default(0);
            $table->string('payment_term_type');
            $table->integer('payment_term')->default(0);
            $table->boolean('taxable_enterprise')->default(false);
            $table->string('tax_id')->nullable();
            $table->integer('status')->default(0);
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'code',
                'name'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
