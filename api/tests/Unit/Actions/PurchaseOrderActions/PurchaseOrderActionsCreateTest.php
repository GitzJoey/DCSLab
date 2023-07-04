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
use Tests\ActionsTestCase;

class PurchaseOrderActionsCreateTest extends ActionsTestCase
{
    private PurchaseOrderActions $purchaseOrderActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderActions = new PurchaseOrderActions();
    }

    public function test_purchase_order_actions_call_create_expect_db_has_record()
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

        $globalDiscountArr = [];
        array_push(
            $globalDiscountArr,
            PurchaseOrderDiscount::factory()->setGlobalDiscountRandom()->make()->toArray()
        );

        $purchaseOrderArr = PurchaseOrder::factory()
            ->for($company)
            ->for($branch)
            ->for($supplier)
            ->make(['global_discount' => $globalDiscountArr])
            ->toArray();

        $ProductUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()
            ->inRandomOrder()->take($ProductUnitCount)
            ->get();

        $purchaseOrderProductUnitArr = [];
        foreach ($productUnits as $productUnit) {
            $perUnitDiscount = [];
            $perUnitSubTotalDiscount = [];

            array_push(
                $purchaseOrderProductUnitArr,
                PurchaseOrderProductUnit::factory()->for($productUnit)->make([
                    'product_id' => $productUnit->product_id,
                    'per_unit_discounts' => $perUnitDiscount,
                    'per_unit_sub_total_discounts' => $perUnitSubTotalDiscount,
                ])->toArray()
            );
        }

        $result = $this->purchaseOrderActions->create(
            $purchaseOrderArr,
            $purchaseOrderProductUnitArr
        );

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $result->id,
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

        for ($i = 0; $i < count($purchaseOrderProductUnitArr); $i++) {
            $this->assertDatabaseHas('purchase_order_product_units', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'purchase_order_id' => $result->id,
                'product_id' => $productUnits[$i]->product_id,
                'product_unit_id' => $purchaseOrderProductUnitArr[$i]['product_unit_id'],
                'qty' => $purchaseOrderProductUnitArr[$i]['qty'],
                'product_unit_amount_per_unit' => $purchaseOrderProductUnitArr[$i]['product_unit_amount_per_unit'],
                'product_unit_initial_price' => $purchaseOrderProductUnitArr[$i]['product_unit_initial_price'],
                'vat_status' => $purchaseOrderProductUnitArr[$i]['vat_status'],
                'vat_rate' => $purchaseOrderProductUnitArr[$i]['vat_rate'],
                'remarks' => $purchaseOrderProductUnitArr[$i]['remarks'],
            ]);

            $perUnitDiscountArr = $purchaseOrderProductUnitArr[$i]['per_unit_discounts'];
            foreach ($perUnitDiscountArr as $perUnitDiscount) {
                $this->assertDatabaseHas('purchase_order_discounts', [
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'purchase_order_id' => $result->id,
                    'discount_type' => $perUnitDiscount['discount_type'],
                    'amount' => $perUnitDiscount['amount'],
                ]);
            }

            $perUnitSubTotalDiscountArr = $purchaseOrderProductUnitArr[$i]['per_unit_sub_total_discounts'];
            foreach ($perUnitSubTotalDiscountArr as $perUnitSubTotalDiscount) {
                $this->assertDatabaseHas('purchase_order_discounts', [
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'purchase_order_id' => $result->id,
                    'discount_type' => $perUnitSubTotalDiscount['discount_type'],
                    'amount' => $perUnitSubTotalDiscount['amount'],
                ]);
            }
        }
    }

    public function test_purchase_order_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseOrderActions->create(
            [],
            [],
            []
        );
    }
}
