<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Services\RoleService;
use Exception;
use App\Models\Role;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($randomPermission = true, $count = 10)
    {
        $instances = Container::getInstance();

        $permissions =  $instances->make(RoleService::class)->getAllPermissions()->pluck('id')->toArray();

        for ($i = 0; $i < $count; $i++) {
            try {
                $role = Role::factory()->create();

                if ($randomPermission) {
                    shuffle($permissions);
                    $maxP = (new RandomGenerator())->generateNumber(1, count($permissions) - 1);

                    for ($j = 0; $j < $maxP; $j++) {
                        $role->attachPermission($permissions[$j]);
                    }
                }
            } catch(Exception $e) {
                $this->command->info($e->getMessage());
            }
        }
    }
}
