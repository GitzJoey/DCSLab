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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->ulid();

            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->string('code');
            $table->dateTime('date');
            $table->foreignId('category_id')->references('id')->on('stock_adjustment_categories');
            $table->foreignId('in_warehouse_id')->nullable()->references('id')->on('warehouses');
            $table->foreignId('out_warehouse_id')->nullable()->references('id')->on('warehouses');
            $table->string('remarks')->nullable();
            $table->boolean('is_posted')->default(false);

            $table->decimal('total_incoming_item_qty', 30, 8)->default(0);
            $table->decimal('total_incoming_item_cogs', 30, 8)->default(0);
            $table->decimal('total_outgoing_item_qty', 30, 8)->default(0);

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
