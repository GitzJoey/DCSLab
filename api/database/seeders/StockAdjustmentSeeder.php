<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentCategory;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockAdjustmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?int $companyId = null, ?int $qtyPerCompany = null): void
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
            $branch = Branch::where('company_id', $company->id)->inRandomOrder()->first();
            if (! $branch) {
                $branch = Branch::factory()->create(['company_id' => $company->id]);
            }

            $category = StockAdjustmentCategory::where('company_id', $company->id)->inRandomOrder()->first();
            if (! $category) {
                $category = StockAdjustmentCategory::factory()->create(['company_id' => $company->id]);
            }

            $warehouse = Warehouse::where('company_id', $company->id)->where('branch_id', $branch->id)->inRandomOrder()->first();
            if (! $warehouse) {
                $warehouse = Warehouse::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
            }

            for ($i = 0; $i < $qtyPerCompany; $i++) {
                $factory = StockAdjustment::factory()
                    ->for($company)
                    ->for($branch)
                    ->for($category, 'category');

                // Randomize type: IN, OUT, or BOTH
                $type = fake()->randomElement(['in', 'out', 'both']);

                if ($type === 'in' || $type === 'both') {
                    $factory = $factory
                        ->for($warehouse, 'inWarehouse')
                        ->withInProducts(rand(1, 3));
                }

                if ($type === 'out' || $type === 'both') {
                    $factory = $factory
                        ->for($warehouse, 'outWarehouse')
                        ->withOutProducts(rand(1, 3));
                }

                $factory->create();
            }
        }
    }
}
