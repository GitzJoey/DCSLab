<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($productPerCompany = 5, $onlyThisCompanyId = 0)
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

        foreach ($companyIds as $companyId) {
            for ($i = 0; $i < $productPerCompany; $i++) {
                $productGroupId = ProductGroup::whereCompanyId($companyId)->inRandomOrder()->limit(1)->value('id');
                $brandId = Brand::whereCompanyId($companyId)->inRandomOrder()->limit(1)->value('id');

                $product = Product::factory()->make([
                    'company_id' => $companyId,
                    'product_group_id' => $productGroupId,
                    'brand_id' => (new RandomGenerator())->randomTrueOrFalse() ? null : $brandId,
                ]);

                $product->save();

                $units = Unit::whereCompanyId($companyId)->get();
                $shuffled_units = $units->shuffle();
                
                $brandId = $product->brand_id;
                if ($brandId) {
                    $howManyUnitsPerProduct = (new RandomGenerator())->generateNumber(1, $units->count());
                } else {
                    $howManyUnitsPerProduct = 1;
                }

                $isbaseIndex = (new RandomGenerator())->generateNumber(0, $howManyUnitsPerProduct - 1);
                $isPrimaryUnitIndex = (new RandomGenerator())->generateNumber(0, $howManyUnitsPerProduct - 1);

                for ($j = 0; $j < $howManyUnitsPerProduct; $j++) {
                    $productUnit = new ProductUnit();

                    $productUnit->company_id = $companyId;
                    $productUnit->code = (new RandomGenerator())->generateFixedLengthNumber(5);
                    $productUnit->product_id = $product->id;
                    $productUnit->unit_id = $shuffled_units[$j]->id;
                    $productUnit->is_base = $j == $isbaseIndex ? 1 : 0;
                    $productUnit->conversion_value = $j == $isbaseIndex ? 1 : (new RandomGenerator())->generateRandomOneZero(3);
                    $productUnit->is_primary_unit = $j == $isPrimaryUnitIndex ? 1 : 0;
                    $productUnit->remarks = '';

                    $product->productUnits()->save($productUnit);
                }
            }
        }
    }
}
