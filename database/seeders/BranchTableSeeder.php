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

        $randomGenerator = new randomGenerator();

        foreach($companies as $c) {
            $rand = $randomGenerator->generateOne($branchPerCompanies - 1);

            for($i = 0; $i < $branchPerCompanies; $i++)
            {
                $makeItActiveStatus = (new RandomGenerator())->randomTrueOrFalse();

                if($makeItActiveStatus) {
                    $branch = Branch::factory()->setStatusActive()->make([
                        'company_id' => $c
                    ]);
                } else {
                    $branch = Branch::factory()->setStatusInactive()->make([
                        'company_id' => $c
                    ]);
                }

                if ($i == $rand) {
                    $branch->is_main = 1;
                    $branch->status = 1;
                }

                $branch->save();
            }
        }
    }
}
