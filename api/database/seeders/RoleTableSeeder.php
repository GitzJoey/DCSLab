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
        $permissions = Permission::get();

        for ($i = 0; $i < $count; $i++) {
            try {
                $role = Role::factory();

                if ($randomPermission) {
                    $maxP = random_int(1, count($permissions) - 1);

                    $shuffledPermissions = $permissions->shuffle()->take($maxP);

                    for ($j = 0; $j < $maxP; $j++) {
                        $role = $role->hasAttached($shuffledPermissions[$j]);
                    }

                    $role->create();
                }
            } catch (Exception $e) {
                dd($e->getMessage());
                $this->command->info($e->getMessage());
            }
        }
    }
}
