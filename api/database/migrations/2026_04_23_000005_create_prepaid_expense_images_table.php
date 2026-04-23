<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prepaid_expense_images', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('prepaid_expense_id')->nullable()->constrained('prepaid_expenses')->onDelete('cascade');
            $table->string('path');
            $table->string('hash');
            $table->boolean('is_main');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prepaid_expense_images');
    }
};
