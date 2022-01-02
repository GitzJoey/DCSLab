<?php

namespace Database\Seeders;

use App\Services\RoleService;
use App\Services\UserService;

use Exception;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\Profile;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($truncate = false, $count = 4)
    {
        if ($truncate) $this->truncateUsersTables();

        $instances = Container::getInstance();

        $faker = \Faker\Factory::create('id_ID');

        for ($i = 0; $i < $count; $i++) {
            try {
                $name = $faker->name;

                $usr = User::factory()->make([
                    'name' => str_replace(' ', '', $name)
                ]);

                $usr->created_at = Carbon::now();
                $usr->updated_at = Carbon::now();

                $usr->save();

                $profile = Profile::factory()->setFirstName($name);

                $profile->created_at = Carbon::now();
                $profile->updated_at = Carbon::now();

                $usr->profile()->save($profile);

                $setting = $instances->make(UserService::class)->createDefaultSetting();
                $roles = $instances->make(RoleService::class)->readBy('NAME', Config::get('const.DEFAULT.ROLE.USER'));

                $usr->attachRoles([$roles->id]);

                $usr->settings()->saveMany($setting);
            } catch (Exception $e) {

            }
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
