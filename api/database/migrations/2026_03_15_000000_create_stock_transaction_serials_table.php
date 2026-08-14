<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_serial_transactions', function (Blueprint $table) {
            $table->id();

            $table->morphs('referable');
            $table->dateTime('date');
            $table->foreignId('warehouse_id')->references('id')->on('warehouses');
            $table->foreignId('product_id')->references('id')->on('products');
            $table->tinyInteger('direction'); // +1 = in, -1 = out
            $table->string('serial');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_serial_transactions');
    }
};
