<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($truncate = false, $count = 4, $role = 'user'): void
    {
        if ($truncate) {
            $this->truncateUsersTables();
        }

        $role = Role::where('name', $role)->first();

        for ($i = 0; $i < $count; $i++) {
            User::factory()
                ->setCreatedAt()->setUpdatedAt()
                ->has(Profile::factory()->setCreatedAt()->setUpdatedAt())
                ->hasAttached($role)
                ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                ->create();
        }
    }

    private function truncateUsersTables()
    {
        Schema::disableForeignKeyConstraints();

        DB::table('users')->truncate();
        DB::table('profiles')->truncate();
        DB::table('settings')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
