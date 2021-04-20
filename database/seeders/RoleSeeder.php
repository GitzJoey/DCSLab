<?php

namespace Database\Seeders;

use Exception;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 19; $i++) {
            try {
                $role = Role::factory()->create();
            } catch(Exception $e) {

            }
        }
    }
}
