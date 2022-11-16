<?php

namespace Database\Seeders;

use App\Enums\ProductGroupCategory;
use App\Models\Company;
use App\Models\ProductGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ProductGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($countPerCompany = 5, $onlyThisCompanyId = 0, $category = 3)
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
            switch ($category) {
                case ProductGroupCategory::PRODUCTS->value:
                    ProductGroup::factory()->setCategoryToProduct()->count($countPerCompany)->create([
                        'company_id' => $c,
                    ]);
                    break;
                case ProductGroupCategory::SERVICES->value:
                    ProductGroup::factory()->setCategoryToService()->count($countPerCompany)->create([
                        'company_id' => $c,
                    ]);
                    break;
                case ProductGroupCategory::PRODUCTS_AND_SERVICES->value:
                default:
                    ProductGroup::factory()->count($countPerCompany)->create([
                        'company_id' => $c,
                    ]);
                    break;
            }
        }
    }
}
