<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Profile;
use App\Models\Supplier;

use App\Models\User;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

class SupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($supplierPerCompany = 10)
    {
        $companies = Company::get()->pluck('id');

        foreach($companies as $c)
        {
            for ($i = 0; $i < $supplierPerCompany; $i++) {
                $usr = User::factory()->count(1)->create()[0];

                $profile = Profile::factory()->setFirstName($usr->name);
                $usr->profile()->save($profile);
    
                $usr->companies()->attach($c);
    
                Supplier::factory()->create([
                    'company_id' => $c,
                    'user_id' => $usr->id
                ]);    
            }
        }
    }
}
