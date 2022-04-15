<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class BranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($branchPerCompanies = 3, $onlyThisCompanyId = 0)
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

        foreach($companies as $c) {
            for($i = 0; $i < $branchPerCompanies; $i++)
            {
                $makeItActiveStatus = (new RandomGenerator())->randomTrueOrFalse();

                if($makeItActiveStatus) {
                    Branch::factory()->setStatusActive()->create([
                        'company_id' => $c
                    ]);
                } else {
                    Branch::factory()->setStatusInactive()->create([
                        'company_id' => $c
                    ]);
                }
            }
        }
    }
}
