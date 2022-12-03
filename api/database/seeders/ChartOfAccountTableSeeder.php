<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use App\Services\ChartOfAccountService;
use Illuminate\Database\Eloquent\Collection;

class ChartOfAccountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($onlyThisCompanyId = 0)
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

        $this->chartOfAccountService = app(ChartOfAccountService::class);

        foreach ($companies as $c) {
             $this->chartOfAccountService->createRootAccount($c);
        }
    }
}
