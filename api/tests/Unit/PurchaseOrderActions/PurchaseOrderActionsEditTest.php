<?php

namespace Tests\Unit;

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
use Database\Seeders\BranchTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\ProductGroupTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Database\Seeders\SupplierTableSeeder;
use Database\Seeders\UnitTableSeeder;
use Exception;
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

        $purchaseOrderArr = $purchaseOrder->toArray();

        $purchaseOrderProductUnitArr = $purchaseOrder->purchaseOrderProductUnits->toArray();
        array_push(
            $purchaseOrderProductUnitArr,
            PurchaseOrderProductUnit::factory()
                ->for($company)->for($branch)->for($purchaseOrder)
                ->for($productUnit->product)
                ->for($productUnit)->make(['id' => null])->toArray()
        );

        $discountArr = [];

        $result = $this->purchaseOrderActions->update(
            $purchaseOrder,
            $purchaseOrderArr,
            $purchaseOrderProductUnitArr,
            $discountArr,
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

        $this->assertDatabaseHas('purchase_order_product_units', [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'purchase_order_id' => $result->id,
            'product_id' => $purchaseOrderProductUnitArr[0]['product_id'],
            'product_unit_id' => $purchaseOrderProductUnitArr[0]['product_unit_id'],
            'qty' => $purchaseOrderProductUnitArr[0]['qty'],
            'product_unit_amount_per_unit' => $purchaseOrderProductUnitArr[0]['product_unit_amount_per_unit'],
            'product_unit_initial_price' => $purchaseOrderProductUnitArr[0]['product_unit_initial_price'],
            'vat_status' => $purchaseOrderProductUnitArr[0]['vat_status'],
            'vat_rate' => $purchaseOrderProductUnitArr[0]['vat_rate'],
            'vat_amount' => $purchaseOrderProductUnitArr[0]['vat_amount'],
            'remarks' => $purchaseOrderProductUnitArr[0]['remarks'],
        ]);
    }

    public function test_purchase_order_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $this->supplierSeeder->callWith(SupplierTableSeeder::class, [3, $companyId]);
        $this->productUnitSeeder->callWith(UnitTableSeeder::class, [3, $companyId]);
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId]);
        $this->purchaseOrderSeeder->callWith(PurchaseOrderTableSeeder::class, [3, $companyId]);

        $purchaseOrder = $company->purchaseOrders()->inRandomOrder()->first();

        $newPurchaseOrderArr = [];
        $productUnitArr = [];
        $discountArr = [];

        $this->purchaseOrderActions->update(
            $purchaseOrder,
            $newPurchaseOrderArr,
            $productUnitArr,
            $discountArr,
        );
    }
}
