<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProductGroup;
use Illuminate\Database\Seeder;
use App\Enums\ProductGroupCategory;
use Illuminate\Database\Eloquent\Collection;

class ProductGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($countPerCompany = 5, $onlyThisCompanyId = 0, $category = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $c = Company::find($onlyThisCompanyId);

            if ($c) {
                $companies = (new Collection())->push($c->id);
            } else {
                $companies = Company::get()->pluck('id');
            }
        } else {
            $companies = Company::get()->pluck('id');
        }

        foreach ($companies as $c) {
            if ($category == 0) {
                ProductGroup::factory()->count($countPerCompany)->create([
                    'company_id' => $c,
                ]);
            }
            if ($category == ProductGroupCategory::PRODUCTS->value) {
                ProductGroup::factory()->setCategoryToProduct()->count($countPerCompany)->create([
                    'company_id' => $c,
                ]);
            }
            if ($category == ProductGroupCategory::SERVICES->value) {
                ProductGroup::factory()->setCategoryToService()->count($countPerCompany)->create([
                    'company_id' => $c,
                ]);
            }
            if ($category == ProductGroupCategory::PRODUCTS_AND_SERVICES->value) {
                ProductGroup::factory()->setCategoryToProductAndService()->count($countPerCompany)->create([
                    'company_id' => $c,
                ]);
            }
        }
    }
}
