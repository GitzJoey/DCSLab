<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name')->nullable();

            $table->foreignId('sales_customer_group_id')->references('id')->on('sales_customer_groups')->onUpdate('cascade')->onDelete('restrict');;
            $table->string('sales_territory')->nullable();

            $table->integer('use_limit_outstanding_notes')->nullable();
            $table->integer('limit_outstanding_notes');
            $table->integer('use_limit_payable_nominal')->nullable();
            $table->decimal('limit_payable_nominal', $precision = 16, $scale = 8);
            $table->integer('use_limit_age_notes')->nullable();
            $table->integer('limit_age_notes');
            $table->integer('term');
            
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('contact')->nullable();
            $table->string('tax_id')->nullable();

            $table->string('remarks')->nullable();
			$table->integer('status')->nullable();
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
        Schema::dropIfExists('sales_customers');
    }
}
