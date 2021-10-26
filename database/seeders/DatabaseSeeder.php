<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            CompanyTableSeeder::class,
            BranchSeeder::class,
            WarehouseTableSeeder::class,
            
            CashTableSeeder::class,
            ProductGroupTableSeeder::class,
            ProductBrandTableSeeder::class,
            UnitTableSeeder::class,

            SupplierTableSeeder::class,

            ProductTableSeeder::class,
            
            //LaratrustSeeder::class,
            //UserSeeder::class,
            //RoleSeeder::class,
        ]);
    }
}
