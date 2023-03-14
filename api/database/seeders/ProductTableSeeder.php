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
                    for ($i = 0; $i < $productPerCompany - 1; $i++) {
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
        $productGroup = ProductGroup::whereRelation('company', 'id', $company->id)->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first();

        $brand = Brand::whereRelation('company', 'id', $company->id)->inRandomOrder()->first();

        $product = Product::factory()
                    ->for($company)
                    ->for($productGroup)
                    ->for($brand)
                    ->setProductTypeAsProduct();

        $units = Unit::whereRelation('company', 'id', $company->id)
                    ->where('category', '=', UnitCategory::PRODUCTS->value)
                    ->inRandomOrder()->get();
        $shuffledUnits = $units->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);

        for ($j = 0; $j < $productUnitCount; $j++) {
            $product = $product->has(
                ProductUnit::factory()
                    ->for($company)->for($shuffledUnits[$j])
                    ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                    ->setIsPrimaryUnit($j == $primaryUnitIdx)
            );
        }

        $product->create();
    }
}

function createService($productPerCompany, $company)
{
    for ($i = 0; $i < $productPerCompany; $i++) {
        $productGroup = ProductGroup::whereRelation('company', 'id', $company->id)->where('category', '=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first();

        $brand = Brand::whereRelation('company', 'id', $company->id)->inRandomOrder()->first();

        $product = Product::factory()
                    ->for($company)
                    ->for($productGroup)
                    ->for($brand)
                    ->setProductTypeAsService();

        $units = Unit::whereRelation('company', 'id', $company->id)
                    ->where('category', '=', UnitCategory::SERVICES->value)
                    ->inRandomOrder()->get();
        $shuffledUnits = $units->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);

        for ($j = 0; $j < $productUnitCount; $j++) {
            $product = $product->has(
                ProductUnit::factory()
                    ->for($company)->for($shuffledUnits[$j])
                    ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                    ->setIsPrimaryUnit($j == $primaryUnitIdx)
            );
        }

        $product->create();
    }
}
