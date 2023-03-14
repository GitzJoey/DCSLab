<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
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
    public function run($supplierPerCompany = 10, $onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        foreach ($companies as $company) {
            for ($i = 0; $i < $supplierPerCompany; $i++) {
                $supplier = Supplier::factory()
                            ->for($company)
                            ->for(
                                User::factory()
                                    ->has(Profile::factory())
                                    ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                            );

                $products = Product::whereRelation('company', 'id', $company->id)->get();

                $supplierProductCount = random_int(1, $products->count());
                
                $shuffledProducts = $products->shuffle()->take($supplierProductCount);

                $mainProductIdx = random_int(0, $supplierProductCount - 1);

                for ($j = 0; $j < $supplierProductCount; $j++) {
                    $supplier = $supplier->has(
                                    SupplierProduct::factory()->for($company)->for($shuffledProducts[$j])
                                        ->setMainProduct($j == $mainProductIdx)
                                );
                }

                $supplier->create();
            }
        }
    }
}
