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
            $table->id();
            $table->ulid()->nullable();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->string('code');
            $table->string('name');
            $table->integer('max_open_invoice')->default(0);
            $table->decimal('max_outstanding_invoice', $precision = 16, $scale = 8)->default(0);
            $table->integer('max_invoice_age')->default(0);
            $table->string('payment_term_type');
            $table->integer('payment_term')->default(0);
            $table->decimal('selling_point', $precision = 8, $scale = 2)->default(0);
            $table->decimal('selling_point_multiple', $precision = 16, $scale = 8)->default(0);
            $table->integer('sell_at_cost')->default(0);
            $table->decimal('price_markup_percent', $precision = 16, $scale = 8)->default(0);
            $table->decimal('price_markup_nominal', $precision = 16, $scale = 8)->default(0);
            $table->decimal('price_markdown_percent', $precision = 16, $scale = 8)->default(0);
            $table->decimal('price_markdown_nominal', $precision = 16, $scale = 8)->default(0);
            $table->integer('round_on')->default(0);
            $table->integer('round_digit')->default(0);
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
