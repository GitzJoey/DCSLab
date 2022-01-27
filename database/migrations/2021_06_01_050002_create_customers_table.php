<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('customer_group_id')->references('id')->on('customer_groups');
            $table->string('code');
            $table->integer('is_member');
            $table->string('name')->nullable();
            $table->string('zone')->nullable();
            $table->integer('max_open_invoice')->default(0);
            $table->decimal('max_outstanding_invoice', $precision = 16, $scale = 8)->default(0);
            $table->integer('max_invoice_age')->default(0);
            $table->integer('payment_term')->default(0);
            $table->string('tax_id')->nullable();
            $table->string('remarks')->nullable();
			$table->integer('status')->default(0);
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
        Schema::dropIfExists('customers');
    }
}
