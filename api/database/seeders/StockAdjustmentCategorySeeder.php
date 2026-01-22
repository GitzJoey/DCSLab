<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\StockAdjustmentCategory;
use Illuminate\Database\Seeder;

class StockAdjustmentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(?int $stockAdjustmentCategoriesPerCompany = null, ?int $companyId = null)
    {
        $stockAdjustmentCategoriesPerCompany = $stockAdjustmentCategoriesPerCompany ?? 5;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            for ($i = 0; $i < $stockAdjustmentCategoriesPerCompany; $i++) {
                StockAdjustmentCategory::factory()
                    ->for($company)
                    ->create();
            }
        }
    }
}
