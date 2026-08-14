<?php

namespace Database\Seeders;

use App\Actions\Product\ProductActions;
use App\Enums\ProductTypeEnum;
use App\Enums\UnitTypeEnum;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Vinkla\Hashids\Facades\Hashids;

class ProductSeeder extends Seeder
{
    public function run(?int $companyId, int $qtyPerCompany = 5)
    {
        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            for ($i = 0; $i < $qtyPerCompany; $i++) {
                $productCategory = ProductCategory::where('company_id', '=', $company->id)->inRandomOrder()->first();
                $brand = Brand::where('company_id', '=', $company->id)->first();

                $productFactory = Product::factory()
                    ->for($company)
                    ->for($productCategory)
                    ->for($brand);

                $productFactory->create();
            }
        }
    }

    public function makeRegularProduct(bool $encode)
    {

    }

    public function makeProductUnits(bool $encode)
    {
        $company_id = Company::inRandomOrder()->first();
        $product_category_id = ProductCategory::inRandomOrder()->where('company_id', '=', $company_id->id)->first();
        $brand_id = Brand::inRandomOrder()->where('company_id', '=', $company_id->id)->first();

        for ($i = 0; $i < 5; $i++) {
            $product_category_id = ProductCategory::inRandomOrder()->where('company_id', '=', $company_id->id)->first();
            $brand_id = Brand::inRandomOrder()->where('company_id', '=', $company_id->id)->first();
        }

        $product = Product::factory()
            ->make([
                'company_id' => $company_id->id,
                'product_category_id' => $encode ? Hashids::encode($product_category_id->id) : $product_category_id->id,
                'brand_id' => $encode ? Hashids::encode($brand_id->id) : $brand_id->id,
                'code' => 'AUTO',
            ]);

        $units = Unit::whereRelation('company', 'id', $company_id->id)
            ->where('type', '=', UnitTypeEnum::PRODUCT->value)
            ->inRandomOrder()->get();
        $shuffledUnits = $units->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);

        for ($j = 0; $j < $productUnitCount; $j++) {
            $product = $product->has(
                ProductUnit::factory()
                    ->for($company_id)->for($shuffledUnits[$j])
                    ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                    ->setIsPrimaryUnit($j == $primaryUnitIdx)
            );
        }

        return $product;
    }

    public function create()
    {
        $productActions = new ProductActions();

        $data = $this->make(encode: false)->toArray();
        $product = ($data['type'] ?? null) === ProductTypeEnum::SERVICE->value
            ? $productActions->createService($data)
            : $productActions->createProduct($data);

        $product = Product::with('company', 'productCategory', 'brand', 'productUnits.unit')->find($product->id);

        return $product;
    }
}
