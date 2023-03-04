<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_journals', function (Blueprint $table) {
            $table->id();
            $table->ulid()->nullable();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('chart_of_account_id')->nullable()->references('id')->on('chart_of_accounts');
            $table->string('ref');
            $table->string('ref_number');
            $table->dateTime('date', $precision = 0);
            $table->string('transaction_type');
            $table->decimal('value', $precision = 16, $scale = 8)->default(0);
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('accounting_journals');
    }
};
