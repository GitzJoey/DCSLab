<?php

namespace Tests\Feature\PurchaseOrderAPI;

use App\Enums\ProductGroupCategory;
use App\Enums\UnitCategory;
use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDiscount;
use App\Models\PurchaseOrderProductUnit;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_order_api_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(5))
                        ->has(Supplier::factory())
                    )->create();

        $this->actingAs($user);

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

        $arr_product_unit_id = [];
        $arr_global_discount_discount_id = [];
        $arr_global_discount_discount_type = [];
        $arr_global_discount_amount = [];
        $arr_product_unit_product_unit_id = [];
        $arr_product_unit_qty = [];
        $arr_product_unit_amount_per_unit = [];
        $arr_product_unit_initial_price = [];
        $arr_product_unit_per_unit_discount_id = [];
        $arr_product_unit_per_unit_discount_discount_type = [];
        $arr_product_unit_per_unit_discount_amount = [];
        $arr_product_unit_per_unit_sub_total_discount_id = [];
        $arr_product_unit_per_unit_sub_total_discount_discount_type = [];
        $arr_product_unit_per_unit_sub_total_discount_amount = [];
        $arr_product_unit_vat_status = [];
        $arr_product_unit_vat_rate = [];
        $arr_product_unit_remarks = [];

        for ($i = 0; $i < 1; $i++) {
            $purchaseOrderDiscount = PurchaseOrderDiscount::factory()->setGlobalDiscountRandom()->make();
            array_push($arr_global_discount_discount_id, '');
            array_push($arr_global_discount_discount_type, $purchaseOrderDiscount->discount_type);
            array_push($arr_global_discount_amount, $purchaseOrderDiscount->amount);
        }

        $ProductUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($ProductUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrderProductUnit = PurchaseOrderProductUnit::factory()->make();
            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_qty, $purchaseOrderProductUnit['qty']);
            array_push($arr_product_unit_amount_per_unit, $purchaseOrderProductUnit->product_unit_amount_per_unit);
            array_push($arr_product_unit_initial_price, $purchaseOrderProductUnit->product_unit_initial_price);
            // array_push($arr_product_unit_per_unit_discount_id, );
            // array_push($arr_product_unit_per_unit_discount_discount_type, );
            // array_push($arr_product_unit_per_unit_discount_amount, );
            // array_push($arr_product_unit_per_unit_sub_total_discount_id, );
            // array_push($arr_product_unit_per_unit_sub_total_discount_discount_type, );
            // array_push($arr_product_unit_per_unit_sub_total_discount_amount, );
            array_push($arr_product_unit_vat_status, $purchaseOrderProductUnit->vat_status);
            array_push($arr_product_unit_vat_rate, $purchaseOrderProductUnit->vat_rate);
            array_push($arr_product_unit_remarks, $purchaseOrderProductUnit->remarks);
        }

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'supplier_id' => Hashids::encode($supplier->id),
            'arr_global_discount_discount_id' => $arr_global_discount_discount_id,
            'arr_global_discount_discount_type' => $arr_global_discount_discount_type,
            'arr_global_discount_amount' => $arr_global_discount_amount,
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_product_unit_id' => $arr_product_unit_product_unit_id,
            'arr_product_unit_qty' => $arr_product_unit_qty,
            'arr_product_unit_amount_per_unit' => $arr_product_unit_amount_per_unit,
            'arr_product_unit_initial_price' => $arr_product_unit_initial_price,
            'arr_product_unit_per_unit_discount_id' => $arr_product_unit_per_unit_discount_id,
            'arr_product_unit_per_unit_discount_discount_type' => $arr_product_unit_per_unit_discount_discount_type,
            'arr_product_unit_per_unit_discount_amount' => $arr_product_unit_per_unit_discount_amount,
            'arr_product_unit_per_unit_sub_total_discount_id' => $arr_product_unit_per_unit_sub_total_discount_id,
            'arr_product_unit_per_unit_sub_total_discount_discount_type' => $arr_product_unit_per_unit_sub_total_discount_discount_type,
            'arr_product_unit_per_unit_sub_total_discount_amount' => $arr_product_unit_per_unit_sub_total_discount_amount,
            'arr_product_unit_vat_status' => $arr_product_unit_vat_status,
            'arr_product_unit_vat_rate' => $arr_product_unit_vat_rate,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.save'), $purchaseOrderArr);

        $api->assertSuccessful();

        $purchaseOrder = PurchaseOrder::where([
            ['company_id', '=', $company->id],
            ['branch_id', '=', $branch->id],
            ['invoice_code', '=', $purchaseOrderArr['invoice_code']],
        ])->first();

        $this->assertDatabaseHas('purchase_orders', [
            'company_id' => Hashids::decode($purchaseOrderArr['company_id'])[0],
            'branch_id' => Hashids::decode($purchaseOrderArr['branch_id'])[0],
            'id' => $purchaseOrder->id,
            'invoice_code' => $purchaseOrderArr['invoice_code'],
            'invoice_date' => $purchaseOrderArr['invoice_date'],
            'shipping_date' => $purchaseOrderArr['shipping_date'],
            'shipping_address' => $purchaseOrderArr['shipping_address'],
            'supplier_id' => Hashids::decode($purchaseOrderArr['supplier_id'])[0],
            'remarks' => $purchaseOrderArr['remarks'],
            'status' => $purchaseOrderArr['status'],
        ]);

        for ($i = 0; $i < count($productUnits); $i++) {
            $this->assertDatabaseHas('purchase_order_product_units', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $productUnits[$i]->product_id,
                'product_unit_id' => Hashids::decode($arr_product_unit_product_unit_id[$i])[0],
                'qty' => $arr_product_unit_qty[$i],
                'product_unit_amount_per_unit' => $arr_product_unit_amount_per_unit[$i],
                'product_unit_initial_price' => $arr_product_unit_initial_price[$i],
                'vat_status' => $arr_product_unit_vat_status[$i],
                'vat_rate' => $arr_product_unit_vat_rate[$i],
                'remarks' => $arr_product_unit_remarks[$i],
            ]);
        }

        for ($i = 0; $i < count($arr_global_discount_discount_type); $i++) {
            $this->assertDatabaseHas('purchase_order_discounts', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_product_unit_id' => null,
                'discount_type' => $arr_global_discount_discount_type[$i],
                'amount' => $arr_global_discount_amount[$i],
            ]);
        }
    }

    public function test_purchase_order_api_call_create_with_empty_array_parameters_expect_exception()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->create();

        $this->actingAs($user);

        $purchaseOrderArr = [];

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.save'), $purchaseOrderArr);

        $api->assertStatus(422);
    }
}
