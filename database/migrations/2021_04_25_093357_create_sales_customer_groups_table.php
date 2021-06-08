<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesCustomerGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_customer_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name')->nullable();

            $table->integer('is_member_card');

            $table->integer('use_limit_outstanding_notes')->nullable();
            $table->integer('limit_outstanding_notes');
            $table->integer('use_limit_payable_nominal')->nullable();
            $table->decimal('limit_payable_nominal', $precision = 16, $scale = 8);
            $table->integer('use_limit_age_notes')->nullable();
            $table->integer('limit_age_notes');
            $table->integer('term');
            
            $table->decimal('selling_point', $precision = 8, $scale = 2);
            $table->decimal('selling_point_multiple', $precision = 16, $scale = 8);

            $table->integer('sell_at_capital_price');
            $table->decimal('global_markup_percent', $precision = 16, $scale = 8);
            $table->decimal('global_markup_nominal', $precision = 16, $scale = 8);
            $table->decimal('global_discount_percent', $precision = 16, $scale = 8);
            $table->decimal('global_discount_nominal', $precision = 16, $scale = 8);

            $table->integer('is_rounding')->nullable();
            $table->integer('round_on')->nullable();
            $table->integer('round_digit')->nullable();
            $table->string('remarks')->nullable();

			$table->foreignId('finance_cash_id')->references('id')->on('finance_cashes')->onUpdate('cascade')->onDelete('restrict');;

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
        Schema::dropIfExists('sales_customer_groups');
    }
}
