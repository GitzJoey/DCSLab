<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($branchPerCompanies = 3)
    {
        $companies = Company::get()->pluck('id');

        foreach($companies as $c) {
            for($i = 0; $i < $branchPerCompanies; $i++)
            {
                $branch = Branch::factory()->create([
                    'company_id' => $c
                ]);
            }
        }
    }
}
