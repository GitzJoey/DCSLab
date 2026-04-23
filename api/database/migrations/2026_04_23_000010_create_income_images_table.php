<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_images', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('income_id')->nullable()->constrained('incomes')->onDelete('cascade');
            $table->string('path');
            $table->string('hash');
            $table->boolean('is_main');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_images');
    }
};
