<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChartOfAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->ulid()->nullable();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->nullable()->references('id')->on('branches');
            $table->foreignId('parent_id')->nullable()->references('id')->on('chart_of_accounts');
            $table->string('code');
            $table->string('name');
            $table->integer('can_have_child');
            $table->string('account_type')->nullable();
            $table->string('remarks')->nullable();
            $table->integer('status');
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
        Schema::dropIfExists('chart_of_accounts');
    }
};
