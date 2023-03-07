<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Exception;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($randomPermission = true, $count = 10)
    {
        $faker = Faker::create();

        $permissions = Permission::get()->pluck('id')->toArray();

        for ($i = 0; $i < $count; $i++) {
            try {
                $role = Role::factory()->create();

                if ($randomPermission) {
                    shuffle($permissions);
                    $maxP = $faker->numberBetween(1, count($permissions) - 1);

                    for ($j = 0; $j < $maxP; $j++) {
                        $role->attachPermission($permissions[$j]);
                    }
                }
            } catch (Exception $e) {
                dd($e->getMessage());
                $this->command->info($e->getMessage());
            }
        }
    }
}
