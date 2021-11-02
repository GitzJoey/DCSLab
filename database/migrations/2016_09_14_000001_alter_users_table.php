<?php

use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Schema;
use \Illuminate\Database\Schema\Blueprint;
use \Illuminate\Database\Migrations\Migration;

Class AlterUsersTable extends Migration
{
    public function up()
    {
        if(Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('password_changed_at')->nullable();
            });
        }

        DB::statement('ALTER TABLE users CHANGE COLUMN password_changed_at password_changed_at TIMESTAMP NULL DEFAULT NULL AFTER password');
    }

    public function down()
    {

    }
}
