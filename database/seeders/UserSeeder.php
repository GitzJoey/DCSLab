<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Profile;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 9; $i++) {
            $usr = User::factory()->make();

            $usr->created_at = Carbon::now();
            $usr->updated_at = Carbon::now();

            $usr->save();

            $profile = Profile::factory()->setFirstName($usr->name);

            $profile->created_at = Carbon::now();
            $profile->updated_at = Carbon::now();

            $usr->profile()->save($profile);
        }
    }
}
