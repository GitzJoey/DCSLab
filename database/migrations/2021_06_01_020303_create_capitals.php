<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCapitals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('capitals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('investor_id')->references('id')->on('investors');
            $table->foreignId('group_id')->references('id')->on('capital_groups');
            $table->foreignId('cash_id')->references('id')->on('cashes')->nullable();
            $table->string('ref_number')->nullable();
            $table->dateTime('date', $precision = 0);
            $table->integer('capital_status')->default(1);
            $table->decimal('amount', 19, 8)->default(0);
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
        Schema::dropIfExists('capitals');
    }
}
