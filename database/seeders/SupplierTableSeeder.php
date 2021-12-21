<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Models\Company;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\User;
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

        $products = Product::get()->pluck('id');

        foreach($companies as $c)
        {
            for ($i = 0; $i < $supplierPerCompany; $i++) {
                $usr = User::factory()->count(1)->create()[0];

                $profile = Profile::factory()->setFirstName($usr->name);
                $usr->profile()->save($profile);
    
                $usr->companies()->attach($c);
    
                $supplier = Supplier::factory()->create([
                    'company_id' => $c,
                    'user_id' => $usr->id
                ]);

                $some_prods = $products->shuffle()
                                ->take((new RandomGenerator())
                                            ->generateNumber(1, $products->count() > 6 ? 6 : $products->count() - 1));

                $suppProd = [];

                foreach($some_prods as $p) {
                    $sp = new SupplierProduct();
                    $sp->company_id = $c;
                    $sp->supplier_id = $supplier->id;
                    $sp->product_id = $p;
                    $sp->main_product = (new RandomGenerator())->randomTrueOrFalse();
                
                    array_push($suppProd, $sp);
                } 

                $supplier->supplierProducts()->saveMany($suppProd);
            }
        }
    }
}
