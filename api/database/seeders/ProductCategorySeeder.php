<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(?int $productCategoriesPerCompany = null, ?int $companyId = null)
    {
        $productCategoriesPerCompany = $productCategoriesPerCompany ?? 5;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            for ($i = 0; $i < $productCategoriesPerCompany; $i++) {
                ProductCategory::factory()
                    ->for($company)
                    ->create();
            }
        }
    }
}
