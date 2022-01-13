<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class BrandTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($brandPerCompany = 15, $onlyThisCompanyId = 0)
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

        foreach($companies as $c)
        {
            Brand::factory()->count($brandPerCompany)->create([
                'company_id' => $c
            ]);
        }
    }
}
