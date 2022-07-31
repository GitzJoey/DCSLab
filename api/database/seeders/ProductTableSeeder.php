<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Enums\UnitCategory;
use App\Models\ProductUnit;
use App\Models\ProductGroup;
use App\Enums\ProductCategory;
use Illuminate\Database\Seeder;
use App\Actions\RandomGenerator;
use App\Enums\ProductGroupCategory;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Collection;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run($productPerCompany = 5, $onlyThisCompanyId = 0, $productCategory = 3)
    {
        if ($onlyThisCompanyId != 0) {
            $company = Company::find($onlyThisCompanyId);

            if ($company) {
                $companyIds = (new Collection())->push($company->id);
            } else {
                $companyIds = Company::get()->pluck('id');
            }
        } else {
            $companyIds = Company::get()->pluck('id');
        }

        $faker = \Faker\Factory::create();
        foreach ($companyIds as $companyId) {
            switch ($productCategory) {
                case ($productCategory == ProductCategory::PRODUCTS->value || $productCategory == ProductCategory::PRODUCTS_AND_SERVICES->value):
                    for ($i = 0; $i < $productPerCompany; $i++) {
                        $productGroupId = ProductGroup::whereCompanyId($companyId)->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id;

                        $brandId = Brand::whereCompanyId($companyId)->inRandomOrder()->first()->id;

                        $unitId = Unit::whereCompanyId($companyId)->where('category', '!=', UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

                        $product = Product::factory()->make([
                            'company_id' => $companyId,
                            'product_group_id' => $productGroupId,
                            'brand_id' => $brandId,
                            'product_type' => $faker->numberBetween(1, 3),
                        ]);
        
                        $product->save();
        
                        $units = Unit::whereCompanyId($companyId)
                        ->whereOr([
                            ['category', '=', UnitCategory::PRODUCTS->value], 
                            ['category', '=', UnitCategory::PRODUCTS_AND_SERVICES->value],
                        ])->take(5)->inRandomOrder()->get();

                        $shuffledUnits = $units->shuffle();
                        $productUnitCount = (new RandomGenerator())->generateNumber(1, $units->count());
                        $primaryUnitIdx = $faker->numberBetween(0, $productUnitCount - 1);
                        $maxConverionValue = 1;
                        for ($j = 0; $j < $productUnitCount; $j++) {
                            $productUnit = new ProductUnit();
        
                            $productUnit->company_id = $companyId;
                            $productUnit->code = ProductUnit::factory()->make()->code;
                            $productUnit->product_id = $product->id;
                            $productUnit->unit_id = $shuffledUnits[$j]->id;
                            $productUnit->is_base = $j == 0 ? 1 : 0;
                            $productUnit->conversion_value = $j == 0 ? 1 : $faker->numberBetween($maxConverionValue + 1, $maxConverionValue + $faker->numberBetween(1, 10));
                            $productUnit->is_primary_unit = $j == $primaryUnitIdx ? 1 : 0;
                            $productUnit->remarks = $faker->sentence();
        
                            $product->productUnits()->save($productUnit);

                            $maxConverionValue = $productUnit->conversion_value;
                        }
                    }
                    if ($productCategory == ProductCategory::PRODUCTS->value) break;
                case ($productCategory == ProductCategory::SERVICES->value || $productCategory == ProductCategory::PRODUCTS_AND_SERVICES->value):
                    for ($i = 0; $i < $productPerCompany; $i++) {
                        $productGroupId = ProductGroup::whereCompanyId($companyId)->where('category', '!=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id;
                        $unitId = Unit::whereCompanyId($companyId)->where('category', '!=', UnitCategory::PRODUCTS->value)->inRandomOrder()->first()->id;

                        $product = Product::factory()->make([
                            'company_id' => $companyId,
                            'product_group_id' => $productGroupId,
                            'brand_id' => null,
                            'product_type' => 4,
                        ]);
        
                        $product->save();
        
                        $productUnit = new ProductUnit();
        
                        $productUnit->company_id = $companyId;
                        $productUnit->code = (new RandomGenerator())->generateFixedLengthNumber(5);
                        $productUnit->product_id = $product->id;
                        $productUnit->unit_id = $unitId;
                        $productUnit->is_base = 1;
                        $productUnit->conversion_value = 1;
                        $productUnit->is_primary_unit = 1;
                        $productUnit->remarks = $faker->sentence();
    
                        $product->productUnits()->save($productUnit);
                    }
                    if ($productCategory == ProductCategory::SERVICES->value) break;
            }
        }
    }
}
