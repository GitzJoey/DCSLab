<?php

namespace Database\Seeders;

use App\Enums\ProductGroupCategory;
use App\Models\Company;
use App\Models\ProductGroup;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

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
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        foreach ($companies as $company) {
            switch ($category) {
                case ProductGroupCategory::PRODUCTS->value:
                    ProductGroup::factory()->for($company)->setCategoryToProduct()->count($countPerCompany)->create();
                    break;
                case ProductGroupCategory::SERVICES->value:
                    ProductGroup::factory()->for($company)->setCategoryToService()->count($countPerCompany)->create();
                    break;
                default:
                    ProductGroup::factory()->for($company)->count($countPerCompany)->state(new Sequence(
                        ['category' => ProductGroupCategory::PRODUCTS->value],
                        ['category' => ProductGroupCategory::SERVICES->value],
                    ))->create();
                    break;
            }
        }
    }
}
