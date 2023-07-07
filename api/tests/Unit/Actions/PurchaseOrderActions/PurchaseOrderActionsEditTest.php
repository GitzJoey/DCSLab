<?php

namespace Tests\Unit\Actions\PurchaseOrderActions;

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
use App\Models\PurchaseOrderDiscount;
use App\Models\PurchaseOrderProductUnit;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\ActionsTestCase;

class PurchaseOrderActionsEditTest extends ActionsTestCase
{
    private PurchaseOrderActions $purchaseOrderActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderActions = new PurchaseOrderActions();
    }

    public function test_purchase_order_actions_call_update_and_insert_product_units_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
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
            ->for($supplier)
            ->has(PurchaseOrderDiscount::factory()->for($company)->for($branch)->setGlobalDiscountRandom(), 'globalDiscounts');

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $productUnitFactory = PurchaseOrderProductUnit::factory()
                ->for($company)
                ->for($branch)
                ->for($productUnit->product)
                ->for($productUnit);

            if (random_int(0, 1)) {
                $productUnitFactory = $productUnitFactory
                    ->has(PurchaseOrderDiscount::factory()
                        ->for($company)
                        ->for($branch)
                        ->for($purchaseOrder)
                        ->setPerUnitDiscountPercent(),
                        'perUnitDiscounts'
                    );
            }

            if (random_int(0, 1)) {
                $productUnitFactory = $productUnitFactory
                    ->has(PurchaseOrderDiscount::factory()
                        ->for($company)
                        ->for($branch)
                        ->for($purchaseOrder)
                        ->setPerUnitSubtotalDiscountPercent(),
                        'perUnitSubTotalDiscounts'
                    );
            }

            $purchaseOrder = $purchaseOrder->has($productUnitFactory, 'productUnits');
        }

        $purchaseOrder = $purchaseOrder->create();

        $purchaseOrderArr = $purchaseOrder->toArray();

        for ($i = 0; $i < 3; $i++) {
            $productUnitArr = $purchaseOrder->productUnits()
                ->with('perUnitDiscounts', 'perUnitSubTotalDiscounts')
                ->get()->toArray();

            $newproductUnit = PurchaseOrderProductUnit::factory()
                ->for($company)->for($branch)->for($purchaseOrder)
                ->for($productUnit->product)
                ->for($productUnit)
                ->make(['id' => null, 'ulid' => Str::ulid()->generate()])
                ->toArray();

            $newproductUnit['per_unit_discounts'] = [];
            if (random_int(0, 1)) {
                $perUnitDiscount = PurchaseOrderDiscount::factory()
                    ->for($company)
                    ->for($branch)
                    ->for($purchaseOrder)
                    ->setPerUnitDiscountPercent()
                    ->make(['id' => null, 'ulid' => Str::ulid()->generate()])
                    ->toArray();
                array_push($newproductUnit['per_unit_discounts'], $perUnitDiscount);
            }

            $newproductUnit['per_unit_sub_total_discounts'] = [];
            if (random_int(0, 1)) {
                $perUnitSubTotalDiscount = PurchaseOrderDiscount::factory()
                    ->for($company)
                    ->for($branch)
                    ->for($purchaseOrder)
                    ->make(['id' => null, 'ulid' => Str::ulid()->generate()])
                    ->toArray();

                array_push($newproductUnit['per_unit_sub_total_discounts'], $perUnitSubTotalDiscount);
            }

            array_push($productUnitArr, $newproductUnit);
        }

        $purchaseOrderArr['global_discount'] = [];

        $result = $this->purchaseOrderActions->update(
            $purchaseOrder,
            $purchaseOrderArr,
            $productUnitArr,
        );

        $this->assertInstanceOf(PurchaseOrder::class, $result);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'company_id' => $purchaseOrderArr['company_id'],
            'branch_id' => $purchaseOrderArr['branch_id'],
            'invoice_code' => $purchaseOrderArr['invoice_code'],
            'invoice_date' => $purchaseOrderArr['invoice_date'],
            'shipping_date' => $purchaseOrderArr['shipping_date'],
            'shipping_address' => $purchaseOrderArr['shipping_address'],
            'supplier_id' => $purchaseOrderArr['supplier_id'],
            'remarks' => $purchaseOrderArr['remarks'],
            'status' => $purchaseOrderArr['status'],
        ]);

        for ($i = 0; $i < count($productUnitArr); $i++) {
            $this->assertDatabaseHas('purchase_order_product_units', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'purchase_order_id' => $result->id,
                'product_id' => $productUnitArr[$i]['product_id'],
                'product_unit_id' => $productUnitArr[$i]['product_unit_id'],
                'qty' => $productUnitArr[$i]['qty'],
                'product_unit_amount_per_unit' => $productUnitArr[$i]['product_unit_amount_per_unit'],
                'product_unit_initial_price' => $productUnitArr[$i]['product_unit_initial_price'],
                'vat_status' => $productUnitArr[$i]['vat_status'],
                'vat_rate' => $productUnitArr[$i]['vat_rate'],
                'remarks' => $productUnitArr[$i]['remarks'],
            ]);

            foreach ($productUnitArr[$i]['per_unit_discounts'] as $perUnitDiscount) {
                $this->assertDatabaseHas('purchase_order_discounts', [
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'purchase_order_id' => $result->id,
                    'order' => $perUnitDiscount['order'],
                    'discount_type' => $perUnitDiscount['discount_type'],
                    'amount' => $perUnitDiscount['amount'],
                ]);
            }

            foreach ($productUnitArr[$i]['per_unit_sub_total_discounts'] as $perUnitSubTotalDiscount) {
                $this->assertDatabaseHas('purchase_order_discounts', [
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'purchase_order_id' => $result->id,
                    'order' => $perUnitSubTotalDiscount['order'],
                    'discount_type' => $perUnitSubTotalDiscount['discount_type'],
                    'amount' => $perUnitSubTotalDiscount['amount'],
                ]);
            }
        }
    }

    public function test_purchase_order_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
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
            ->for($supplier)
            ->has(PurchaseOrderDiscount::factory()->for($company)->for($branch)->setGlobalDiscountRandom());

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(
                productUnit::factory()
                    ->for($company)->for($branch)
                    ->for($productUnit->product)
                    ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $PurchaseOrderArr = [];
        $productUnitArr = [];

        $this->purchaseOrderActions->update(
            $purchaseOrder,
            $PurchaseOrderArr,
            $productUnitArr,
        );
    }
}
