<?php

namespace Database\Seeders;

use App\Enums\UnitCategory;
use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UnitTableSeeder extends Seeder
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
                case UnitCategory::PRODUCTS->value:
                    Unit::factory()->for($company)->setCategoryToProduct()->count($countPerCompany)->create();
                    break;
                case UnitCategory::SERVICES->value:
                    Unit::factory()->for($company)->setCategoryToService()->count($countPerCompany)->create();
                    break;
                default:
                    $countPerCompany = $countPerCompany < 3 ? 3 : $countPerCompany;

                    Unit::factory()->for($company)->count($countPerCompany)->state(new Sequence(
                        ['category' => UnitCategory::PRODUCTS->value],
                        ['category' => UnitCategory::SERVICES->value],
                    ))->create();
                    break;
            }
        }
    }
}
