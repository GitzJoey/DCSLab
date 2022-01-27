<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('expense_group_id')->references('id')->on('expense_groups');
            $table->foreignId('cash_id')->nullable()->references('id')->on('cashes');
            $table->string('code');
            $table->dateTime('date', $precision = 0);
            $table->integer('payment_term_type');
            $table->decimal('amount', 19, 8)->default(0);
            $table->decimal('amount_owed', 19, 8)->default(0);
            $table->string('remarks')->nullable();
            $table->integer('posted');
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
