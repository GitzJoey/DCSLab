<?php

namespace Database\Seeders;

use App\Services\RoleService;
use App\Services\UserService;
use Exception;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($truncate = false, $count = 19)
    {
        if ($truncate) $this->truncateUsersTables();

        $instances = Container::getInstance();

        for ($i = 0; $i < $count; $i++) {
            try {
                $usr = User::factory()->make();

                $usr->created_at = Carbon::now();
                $usr->updated_at = Carbon::now();

                $usr->save();

                $profile = Profile::factory()->setFirstName($usr->name);

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
