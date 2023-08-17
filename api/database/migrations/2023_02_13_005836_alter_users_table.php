<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->ulid();
                $table->timestamp('password_changed_at')->nullable();
            });
        }

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver != 'sqlite') {
            DB::statement('ALTER TABLE users CHANGE COLUMN ulid ulid CHAR(36) NULL DEFAULT NULL AFTER id');
            DB::statement('ALTER TABLE users CHANGE COLUMN password_changed_at password_changed_at TIMESTAMP NULL DEFAULT NULL AFTER password');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
