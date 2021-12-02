<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Models\Company;
use App\Models\Product;

use App\Models\Brand;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($productPerCompany = 5)
    {
        $companies = Company::get()->pluck('id');

        foreach($companies as $c)
        {
            for ($i = 0; $i < $productPerCompany; $i++) {
                $pbId = Brand::whereCompanyId($c)->inRandomOrder()->limit(1)->value('id');
                $gId = ProductGroup::whereCompanyId($c)->inRandomOrder()->limit(1)->value('id');
                $sId = Supplier::whereCompanyId($c)->inRandomOrder()->limit(1)->value('id');

                $prod = Product::factory()->make([
                    'company_id' => $c,
                    'product_group_id' => (new RandomGenerator())->randomTrueOrFalse() ? null : $gId,
                    'brand_id' => $pbId,
                    'supplier_id' => (new RandomGenerator())->randomTrueOrFalse() ? null : $sId,
                ]);

                $prod->save();

                $units = Unit::whereCompanyId($c)->get();

                $howmanyUnitsPerProduct = (new RandomGenerator())->generateNumber(1, $units->count());

                $isbaseIndex = (new RandomGenerator())->generateNumber(0, $howmanyUnitsPerProduct - 1);
                $isPrimaryUnitIndex = (new RandomGenerator())->generateNumber(0, $howmanyUnitsPerProduct - 1);

                for($j = 0; $j < $howmanyUnitsPerProduct; $j++) {
                    $rUnitId = Unit::whereCompanyId($c)->inRandomOrder()->limit(1)->value('id');

                    $pu = new ProductUnit();

                    $pu->company_id = $c;
                    $pu->product_id = $prod->id;
                    $pu->unit_id = $rUnitId;
                    $pu->is_base = $j == $isbaseIndex ? 1:0;
                    $pu->conversion_value = $j == $isbaseIndex ? 1: (new RandomGenerator())->generateRandomOneZero(3);
                    $pu->is_primary_unit = $j == $isPrimaryUnitIndex ? 1:0;
                    $pu->remarks = '';

                    $prod->productUnits()->save($pu);
                }
            }
        }
    }
}
