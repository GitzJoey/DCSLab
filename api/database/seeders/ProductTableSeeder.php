<?php

namespace Database\Seeders;

use App\Enums\ProductCategory;
use App\Enums\ProductGroupCategory;
use App\Enums\UnitCategory;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    public function run($productPerCompany = 5, $onlyThisCompanyId = 0, $productCategory = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        foreach ($companies as $company) {
            switch ($productCategory) {
                case ProductCategory::PRODUCTS->value:
                    createProduct($productPerCompany, $company);
                    break;
                case ProductCategory::SERVICES->value:
                    createService($productPerCompany, $company);
                    break;
                default:
                    createProduct(1, $company);
                    createService(1, $company);

                    for ($i = 0; $i < $productPerCompany - 2; $i++) {
                        if (boolval(random_int(0, 1))) {
                            createProduct(1, $company);
                        } else {
                            createService(1, $company);
                        }
                    }
                    break;
            }
        }
    }
}

function createProduct($productPerCompany, $company)
{
    for ($i = 0; $i < $productPerCompany; $i++) {
        $productGroup = ProductGroup::whereCompany($company)->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first();

        $brand = Brand::whereCompany($company)->inRandomOrder()->first();

        $product = Product::factory()
                    ->for($company)
                    ->for($productGroup)
                    ->for($brand)
                    ->setProductTypeAsProduct()
                    ->create();

        $product->save();

        $units = Unit::whereCompany($company)
            ->where('category', '=', UnitCategory::PRODUCTS->value)
            ->take(5)->inRandomOrder()->get();

        $shuffledUnits = $units->shuffle();
        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);
        $maxConverionValue = 1;

        for ($j = 0; $j < $productUnitCount; $j++) {
            $productUnit = new ProductUnit();

            $productUnit->company_id = $company->id;
            $productUnit->code = ProductUnit::factory()->make()->code;
            $productUnit->product_id = $product->id;
            $productUnit->unit_id = $shuffledUnits[$j]->id;
            $productUnit->is_base = $j == 0 ? 1 : 0;
            $productUnit->conversion_value = $j == 0 ? 1 : random_int($maxConverionValue + 1, $maxConverionValue + random_int(1, 10));
            $productUnit->is_primary_unit = $j == $primaryUnitIdx ? 1 : 0;
            $productUnit->remarks = '';

            $product->productUnits()->save($productUnit);

            $maxConverionValue = $productUnit->conversion_value;
        }
    }
}

function createService($productPerCompany, $company)
{
    for ($i = 0; $i < $productPerCompany; $i++) {
        $productGroup = ProductGroup::whereCompany($company)->where('category', '=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first();

        $unit = Unit::whereCompanyId($company)->where('category', '=', UnitCategory::SERVICES->value)->inRandomOrder()->first();

        $product = Product::factory()
                    ->for($company)
                    ->for($productGroup)
                    ->setProductTypeAsService()
                    ->create();
    }
}
