<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($truncate = false, $count = 4, $role = 'user')
    {
        if ($truncate) {
            $this->truncateUsersTables();
        }

        for ($i = 0; $i < $count; $i++) {
            User::factory()
                ->setName(fake('id_ID')->name)
                ->setCreatedAt()->setUpdatedAt()
                ->has(Profile::factory()->setCreatedAt()->setUpdatedAt()->setFirstName(fake('id_ID')->firstName))
                ->hasAttached(Role::where('name', '=', $role)->first())
                ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                ->create();
        }
    }

    public function truncateUsersTables()
    {
        Schema::disableForeignKeyConstraints();

        DB::table('users')->truncate();
        DB::table('profiles')->truncate();
        DB::table('settings')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
