<?php

namespace Tests\Unit\PurchaseOrderActions;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\Enums\ProductGroupCategory;
use App\Enums\UnitCategory;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProductUnit;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseOrderActionsDeleteTest extends ActionsTestCase
{
    private PurchaseOrderActions $purchaseOrderActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderActions = new PurchaseOrderActions();
    }

    public function test_purchase_order_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
                    ->has(Supplier::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $supplier = $company->suppliers()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 10);
        for ($i = 0; $i < $productSeedCount; $i++) {
            $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                ->inRandomOrder()->first();

            $brand = $company->brands()->inRandomOrder()->first();

            $product = Product::factory()
                ->for($company)
                ->for($productGroup)
                ->for($brand)
                ->setProductTypeAsProduct();

            $units = $company->units()->where('category', '=', UnitCategory::PRODUCTS->value)
                ->inRandomOrder()->get()->shuffle();

            $productUnitCount = random_int(1, $units->count());
            $primaryUnitIdx = random_int(0, $productUnitCount - 1);

            for ($j = 0; $j < $productUnitCount; $j++) {
                $product = $product->has(
                    ProductUnit::factory()
                        ->for($company)->for($units[$j])
                        ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                        ->setIsPrimaryUnit($j == $primaryUnitIdx)
                );
            }

            $product->create();
        }

        $purchaseOrder = PurchaseOrder::factory()
            ->for($company)
            ->for($branch)
            ->for($supplier);

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(
                PurchaseOrderProductUnit::factory()
                    ->for($company)->for($branch)
                    ->for($productUnit->product)
                    ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $result = $this->purchaseOrderActions->delete($purchaseOrder);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_orders', [
            'id' => $purchaseOrder->id,
        ]);
    }
}
