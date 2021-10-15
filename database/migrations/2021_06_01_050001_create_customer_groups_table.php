<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->foreignId('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('restrict');
            
            $table->foreignId('cash_id')->references('id')->on('cashes')->onUpdate('cascade')->onDelete('restrict')->nullable();

            $table->string('code');
            $table->string('name')->nullable();

            $table->integer('is_member_card');

            $table->integer('use_limit_outstanding_notes')->nullable();
            $table->integer('limit_outstanding_notes')->default(0);
            $table->integer('use_limit_payable_nominal')->nullable();
            $table->decimal('limit_payable_nominal', $precision = 16, $scale = 8)->default(0);
            $table->integer('use_limit_age_notes')->nullable();
            $table->integer('limit_age_notes')->default(0);
            $table->integer('term')->default(0);
            
            $table->decimal('selling_point', $precision = 8, $scale = 2)->default(0);
            $table->decimal('selling_point_multiple', $precision = 16, $scale = 8)->default(0);

            $table->integer('sell_at_capital_price')->nullable();
            $table->decimal('global_markup_percent', $precision = 16, $scale = 8)->default(0);
            $table->decimal('global_markup_nominal', $precision = 16, $scale = 8)->default(0);
            $table->decimal('global_discount_percent', $precision = 16, $scale = 8)->default(0);
            $table->decimal('global_discount_nominal', $precision = 16, $scale = 8)->default(0);

            $table->integer('is_rounding')->nullable();
            $table->integer('round_on')->default(0);
            $table->integer('round_digit')->nullable();
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
        Schema::dropIfExists('customer_groups');
    }
}
