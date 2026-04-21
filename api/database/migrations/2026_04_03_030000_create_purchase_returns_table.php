<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            // Header
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->foreignId('supplier_id')->references('id')->on('suppliers');
            $table->foreignId('purchase_id')->nullable()->references('id')->on('purchases');

            // Header advanced
            $table->string('remarks')->nullable();
            $table->boolean('is_posted')->default(false);

            // Footer totals
            $table->decimal('item_total_before_global_discount', 30, 8)->default(0);
            $table->decimal('global_discount', 30, 8)->default(0);
            $table->decimal('item_total_after_global_discount', 30, 8)->default(0);
            $table->decimal('vat_base', 30, 8)->default(0);
            $table->decimal('vat', 30, 8)->default(0);
            $table->decimal('item_total_after_vat', 30, 8)->default(0);
            $table->decimal('additional_cost', 30, 8)->default(0);
            $table->decimal('rounding', 30, 8)->default(0);
            $table->decimal('amount_payable', 30, 8)->default(0);
            // nilai retur yang dipakai memotong purchase lain
            $table->decimal('amount_allocated_to_purchase', 30, 8)->default(0);
            // uang yang benar-benar sudah diterima dari supplier
            $table->decimal('amount_received_total', 30, 8)->default(0);
            // total penyelesaian retur
            $table->decimal('amount_settled_total', 30, 8)->default(0);
            // sisa retur yang belum dipakai dan belum dibayar balik
            $table->decimal('amount_available', 30, 8)->default(0);
            // status akhir kalau retur sudah habis terselesaikan
            $table->boolean('is_settled')->default(false);

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
        Schema::dropIfExists('purchase_returns');
    }
};
