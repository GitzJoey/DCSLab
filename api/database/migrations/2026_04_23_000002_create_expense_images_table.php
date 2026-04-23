<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_images', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('expense_id')->nullable()->constrained('expenses')->onDelete('cascade');
            $table->string('path');
            $table->string('hash');
            $table->boolean('is_main');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_images');
    }
};
