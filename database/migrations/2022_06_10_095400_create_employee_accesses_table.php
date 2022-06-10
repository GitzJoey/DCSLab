<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmployeeAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('employee_id')->references('id')->on('employees');
            $table->foreignId('branch_id')->references('id')->on('branches');

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'employee_id', 'branch_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_accesses');
    }
};
