<?php

namespace Database\Seeders;

use App\Actions\Role\RoleActions;
use App\Enums\UserRoles;
use App\Models\Profile;
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

        $faker = \Faker\Factory::create('id_ID');

        $roleActions = new RoleActions();

        for ($i = 0; $i < $count; $i++) {
            $name = $faker->name;

            $usr = User::factory()->setCreatedAt()->setUpdatedAt()->create();

            $profile = Profile::factory()->setCreatedAt()->setUpdatedAt()->setFirstName($name);

            $usr->profile()->save($profile);

            $setting = Setting::factory()->createDefaultSetting();

            $usr->settings()->saveMany($setting);

            $roles = $roleActions->readBy('NAME', $role);

            if (! $roles) {
                $roles = $roleActions->readBy('NAME', UserRoles::USER->value);
            }

            $usr->attachRoles([$roles->id]);
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
