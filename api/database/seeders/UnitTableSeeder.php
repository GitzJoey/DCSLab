<?php

namespace Database\Seeders;

use App\Enums\UnitCategory;
use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

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
                case UnitCategory::PRODUCTS->value:
                    Unit::factory()->setCategoryToProduct()->count($countPerCompany)->create([
                        'company_id' => $c,
                    ]);
                    break;
                case UnitCategory::SERVICES->value:
                    Unit::factory()->setCategoryToService()->count($countPerCompany)->create([
                        'company_id' => $c,
                    ]);
                    break;
                default:
                    $countPerCompany = $countPerCompany < 3 ? 3 : $countPerCompany;
                    Unit::factory()->count($countPerCompany)->state(new Sequence(
                        ['category' => UnitCategory::PRODUCTS->value],
                        ['category' => UnitCategory::SERVICES->value],
                    ))->create([
                        'company_id' => $c,
                    ]);
                    break;
            }
        }
    }
}
