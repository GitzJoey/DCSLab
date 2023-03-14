<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\Profile;
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
                                ->has(Setting::factory()->createDefaultSetting())
                            )
                            ->create();

                $productIds = Product::whereRelation('company', 'id', $company->id)->get()->pluck('id');

                $productCount = $productIds->count();
                $productCount = $productCount > 6 ? 6 : $productCount - 1;
                $supplierProductCount = random_int(1, $productCount);
                $ProductIds = $productIds->shuffle()->take($supplierProductCount);

                $supplierProducts = [];

                foreach ($ProductIds as $productId) {
                    $supplierProduct = new SupplierProduct();
                    $supplierProduct->company_id = $company->id;
                    $supplierProduct->supplier_id = $supplier->id;
                    $supplierProduct->product_id = $productId;
                    $supplierProduct->main_product = boolval(random_int(0, 1));

                    array_push($supplierProducts, $supplierProduct);
                }

                $supplier->supplierProducts()->saveMany($supplierProducts);
            }
        }
    }
}
