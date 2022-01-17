<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->string('code');
            $table->string('name')->nullable();
            $table->integer('payment_term_type');
            $table->string('contact')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->integer('taxable_enterprise')->default(0);
            $table->string('tax_id')->nullable();
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
        Schema::dropIfExists('suppliers');
    }
}
