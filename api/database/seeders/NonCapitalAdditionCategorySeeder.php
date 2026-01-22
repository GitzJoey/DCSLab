<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\NonCapitalAdditionCategory;
use Illuminate\Database\Seeder;

class NonCapitalAdditionCategorySeeder extends Seeder
{
    public function run(?int $companyId, ?int $qtyPerCompany)
    {
        $query = Company::query();
        if ($companyId) {
            $query->where('id', '=', $companyId);
        }
        $companies = $query->get();

        if (! $qtyPerCompany) {
            $qtyPerCompany = 5;
        }
        foreach ($companies as $company) {
            for ($i = 0; $i < $qtyPerCompany; $i++) {
                $nonCapitalAdditionCategoryFactory = NonCapitalAdditionCategory::factory()->for($company);
                $nonCapitalAdditionCategoryFactory->create();
            }
        }
    }
}
