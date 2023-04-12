<?php

namespace Database\Seeders;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\Company;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class CustomerGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($countPerCompany = 5, $onlyThisCompanyId = 0)
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
            $customerGroup = CustomerGroup::factory()->make([
                'company_id' => $c,
            ]);

            $customerGroupArr = $customerGroup->toArray();

            $customerGroupActions = app(CustomerGroupActions::class);
            $customerGroup = $customerGroupActions->create($customerGroupArr);
        }
    }
}
