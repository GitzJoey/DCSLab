<?php

namespace Database\Seeders;

use App\Actions\Role\RoleActions;
use App\Models\Role;
use App\Services\RoleService;
use Exception;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

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

        $roleActions = new RoleActions();

        $permissions = $roleActions->getAllPermissions()->pluck('id')->toArray();

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
