<?php

namespace Tests\Feature\API\PurchaseOrderAPI;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
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
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
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

        $productSeedCount = 10;
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
            $primaryUnitorder = random_int(0, $productUnitCount - 1);

            for ($j = 0; $j < $productUnitCount; $j++) {
                $product = $product->has(
                    ProductUnit::factory()
                        ->for($company)->for($units[$j])
                        ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                        ->setIsPrimaryUnit($j == $primaryUnitorder)
                );
            }

            $product->create();
        }

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_global_discount_id = [];
        $arr_global_discount_ulid = [];
        $arr_global_discount_order = [];
        $arr_global_discount_discount_type = [];
        $arr_global_discount_amount = [];
        $arr_product_unit_product_id = [];
        $arr_product_unit_product_unit_id = [];
        $arr_product_unit_qty = [];
        $arr_product_unit_amount_per_unit = [];
        $arr_product_unit_initial_price = [];
        $arr_product_unit_per_unit_discount_id = [];
        $arr_product_unit_per_unit_discount_ulid = [];
        $arr_product_unit_per_unit_discount_order = [];
        $arr_product_unit_per_unit_discount_discount_type = [];
        $arr_product_unit_per_unit_discount_amount = [];
        $arr_product_unit_per_unit_sub_total_discount_id = [];
        $arr_product_unit_per_unit_sub_total_discount_ulid = [];
        $arr_product_unit_per_unit_sub_total_discount_order = [];
        $arr_product_unit_per_unit_sub_total_discount_discount_type = [];
        $arr_product_unit_per_unit_sub_total_discount_amount = [];
        $arr_product_unit_vat_status = [];
        $arr_product_unit_vat_rate = [];
        $arr_product_unit_remarks = [];

        $ProductUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($ProductUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrderProductUnit = PurchaseOrderProductUnit::factory()->make();
            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_ulid, '');
            array_push($arr_product_unit_product_id, Hashids::encode($productUnit->product_id));
            array_push($arr_product_unit_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_qty, $purchaseOrderProductUnit['qty']);
            array_push($arr_product_unit_amount_per_unit, $purchaseOrderProductUnit->product_unit_amount_per_unit);
            array_push($arr_product_unit_initial_price, $purchaseOrderProductUnit->product_unit_initial_price);
            array_push($arr_product_unit_per_unit_discount_id, []);
            array_push($arr_product_unit_per_unit_discount_ulid, []);
            array_push($arr_product_unit_per_unit_discount_order, []);
            array_push($arr_product_unit_per_unit_discount_discount_type, []);
            array_push($arr_product_unit_per_unit_discount_amount, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_id, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_ulid, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_order, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_discount_type, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_amount, []);
            array_push($arr_product_unit_vat_status, $purchaseOrderProductUnit->vat_status);
            array_push($arr_product_unit_vat_rate, $purchaseOrderProductUnit->vat_rate);
            array_push($arr_product_unit_remarks, $purchaseOrderProductUnit->remarks);
        }

        $purchaseOrderActions = new PurchaseOrderActions();
        for ($i = 0; $i < count($arr_product_unit_product_unit_id); $i++) {
            for ($j = 0; $j < 3; $j++) {
                if (random_int(0, 1)) {
                    $maxValue = $arr_product_unit_initial_price[$i];

                    if (count($arr_product_unit_per_unit_discount_order[$i])) {
                        $maxValue = $maxValue - $purchaseOrderActions->calculatePerUnitDiscountFromFreeArray(
                            $arr_product_unit_initial_price[$i],
                            $arr_product_unit_per_unit_discount_order[$i],
                            $arr_product_unit_per_unit_discount_discount_type[$i],
                            $arr_product_unit_per_unit_discount_amount[$i],
                        );
                    }

                    $purchasePerUnitDiscount = PurchaseOrderDiscount::factory()->setPerUnitDiscountRandom($maxValue)->make();
                    array_push($arr_product_unit_per_unit_discount_id[$i], '');
                    array_push($arr_product_unit_per_unit_discount_ulid[$i], '');
                    array_push($arr_product_unit_per_unit_discount_order[$i], count($arr_product_unit_per_unit_discount_order[$i]));
                    array_push($arr_product_unit_per_unit_discount_discount_type[$i], $purchasePerUnitDiscount->discount_type);
                    array_push($arr_product_unit_per_unit_discount_amount[$i], $purchasePerUnitDiscount->amount);
                }
            }

            for ($j = 0; $j < 3; $j++) {
                if (random_int(0, 1)) {
                    $qty = $arr_product_unit_qty[$i];

                    $initialPrice = $arr_product_unit_initial_price[$i];

                    $perUnitDiscount = $purchaseOrderActions->calculatePerUnitDiscountFromFreeArray(
                        $arr_product_unit_initial_price[$i],
                        $arr_product_unit_per_unit_discount_order[$i],
                        $arr_product_unit_per_unit_discount_discount_type[$i],
                        $arr_product_unit_per_unit_discount_amount[$i],
                    );

                    $priceAfterDisc = $initialPrice - $perUnitDiscount;

                    $subTotal = $qty * $priceAfterDisc;

                    $maxValue = $subTotal;
                    if (count($arr_product_unit_per_unit_sub_total_discount_order[$i])) {
                        $maxValue = $maxValue - $purchaseOrderActions->calculatePerUnitSubTotalDiscountFromFreeArray(
                            $subTotal,
                            $arr_product_unit_per_unit_sub_total_discount_order[$i],
                            $arr_product_unit_per_unit_sub_total_discount_discount_type[$i],
                            $arr_product_unit_per_unit_sub_total_discount_amount[$i]
                        );
                    }

                    $purchasePerUnitSubTotalDiscount = PurchaseOrderDiscount::factory()->setPerUnitSubTotalDiscountRandom($maxValue)->make();
                    array_push($arr_product_unit_per_unit_sub_total_discount_id[$i], '');
                    array_push($arr_product_unit_per_unit_sub_total_discount_ulid[$i], '');
                    array_push($arr_product_unit_per_unit_sub_total_discount_order[$i], count($arr_product_unit_per_unit_sub_total_discount_order[$i]));
                    array_push($arr_product_unit_per_unit_sub_total_discount_discount_type[$i], $purchasePerUnitSubTotalDiscount->discount_type);
                    array_push($arr_product_unit_per_unit_sub_total_discount_amount[$i], $purchasePerUnitSubTotalDiscount->amount);
                }
            }
        }

        $arrProductUnit = [];
        for ($i = 0; $i < count($arr_product_unit_product_unit_id); $i++) {
            $qty = $arr_product_unit_qty[$i];

            $initialPrice = $arr_product_unit_initial_price[$i];

            $perUnitDiscount = $purchaseOrderActions->calculatePerUnitDiscountFromFreeArray(
                $arr_product_unit_initial_price[$i],
                $arr_product_unit_per_unit_discount_order[$i],
                $arr_product_unit_per_unit_discount_discount_type[$i],
                $arr_product_unit_per_unit_discount_amount[$i]
            );

            $priceAfterDisc = $initialPrice - $perUnitDiscount;

            $subTotal = $qty * $priceAfterDisc;

            $perUnitSubTotalDiscount = $purchaseOrderActions->calculatePerUnitSubTotalDiscountFromFreeArray(
                $subTotal,
                $arr_product_unit_per_unit_sub_total_discount_order[$i],
                $arr_product_unit_per_unit_sub_total_discount_discount_type[$i],
                $arr_product_unit_per_unit_sub_total_discount_amount[$i]
            );

            $total = $subTotal - $perUnitSubTotalDiscount;

            array_push($arrProductUnit, [
                'qty' => $qty,
                'initial_price' => $initialPrice,
                'per_unit_disc' => $perUnitDiscount,
                'price_after_disc' => $priceAfterDisc,
                'sub_total' => $subTotal,
                'per_unit_sub_total_discount' => $perUnitSubTotalDiscount,
                'total' => $total,
                'global_disc' => 0,
                'grand_total' => 0,
                'final_price' => 0,
            ]);
        }

        $grandTotal = 0;
        foreach ($arrProductUnit as $productUnit) {
            $grandTotal = $grandTotal + $productUnit['total'];
        }

        $maxValue = $grandTotal;

        for ($i = 0; $i < 3; $i++) {
            if (random_int(0, 1)) {
                $maxValue = $grandTotal - $purchaseOrderActions->calculateGlobalDiscountFromFreeArray(
                    $grandTotal,
                    $arr_global_discount_order,
                    $arr_global_discount_discount_type,
                    $arr_global_discount_amount
                );

                $purchaseOrderDiscount = PurchaseOrderDiscount::factory()->setGlobalDiscountRandom($maxValue)->make();
                array_push($arr_global_discount_id, '');
                array_push($arr_global_discount_ulid, '');
                array_push($arr_global_discount_order, count($arr_global_discount_order));
                array_push($arr_global_discount_discount_type, $purchaseOrderDiscount->discount_type);
                array_push($arr_global_discount_amount, $purchaseOrderDiscount->amount);
            }
        }

        $globalDiscount = $purchaseOrderActions->calculateGlobalDiscountFromFreeArray(
            $grandTotal,
            $arr_global_discount_order,
            $arr_global_discount_discount_type,
            $arr_global_discount_amount,
        );

        foreach ($arrProductUnit as $j => $productUnit) {
            $arrProductUnit[$j]['global_disc'] = $productUnit['total'] / $grandTotal * $globalDiscount;
            $arrProductUnit[$j]['grand_total'] = $productUnit['total'] - $arrProductUnit[$j]['global_disc'];
            $arrProductUnit[$j]['final_price'] = $arrProductUnit[$j]['grand_total'] / $productUnit['qty'];
        }

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'supplier_id' => Hashids::encode($supplier->id),
            'arr_global_discount_id' => $arr_global_discount_id,
            'arr_global_discount_ulid' => $arr_global_discount_ulid,
            'arr_global_discount_order' => $arr_global_discount_order,
            'arr_global_discount_discount_type' => $arr_global_discount_discount_type,
            'arr_global_discount_amount' => $arr_global_discount_amount,
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_product_id' => $arr_product_unit_product_id,
            'arr_product_unit_product_unit_id' => $arr_product_unit_product_unit_id,
            'arr_product_unit_qty' => $arr_product_unit_qty,
            'arr_product_unit_amount_per_unit' => $arr_product_unit_amount_per_unit,
            'arr_product_unit_initial_price' => $arr_product_unit_initial_price,
            'arr_product_unit_per_unit_discount_id' => $arr_product_unit_per_unit_discount_id,
            'arr_product_unit_per_unit_discount_ulid' => $arr_product_unit_per_unit_discount_ulid,
            'arr_product_unit_per_unit_discount_order' => $arr_product_unit_per_unit_discount_order,
            'arr_product_unit_per_unit_discount_discount_type' => $arr_product_unit_per_unit_discount_discount_type,
            'arr_product_unit_per_unit_discount_amount' => $arr_product_unit_per_unit_discount_amount,
            'arr_product_unit_per_unit_sub_total_discount_id' => $arr_product_unit_per_unit_sub_total_discount_id,
            'arr_product_unit_per_unit_sub_total_discount_ulid' => $arr_product_unit_per_unit_sub_total_discount_ulid,
            'arr_product_unit_per_unit_sub_total_discount_order' => $arr_product_unit_per_unit_sub_total_discount_order,
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

        for ($i = 0; $i < count($arr_product_unit_per_unit_discount_discount_type); $i++) {
            if (array_key_exists($i, $arr_product_unit_per_unit_discount_discount_type)) {
                for ($j = 0; $j < count($arr_product_unit_per_unit_discount_discount_type[$i]); $j++) {
                    $this->assertDatabaseHas('purchase_order_discounts', [
                        'company_id' => $company->id,
                        'branch_id' => $branch->id,
                        'purchase_order_id' => $purchaseOrder->id,
                        'order' => $arr_product_unit_per_unit_discount_order[$i][$j],
                        'discount_type' => $arr_product_unit_per_unit_discount_discount_type[$i][$j],
                        'amount' => $arr_product_unit_per_unit_discount_amount[$i][$j],
                    ]);
                }
            }
        }

        for ($i = 0; $i < count($arr_product_unit_per_unit_sub_total_discount_discount_type); $i++) {
            if (array_key_exists($i, $arr_product_unit_per_unit_sub_total_discount_discount_type)) {
                for ($j = 0; $j < count($arr_product_unit_per_unit_sub_total_discount_discount_type[$i]); $j++) {
                    $this->assertDatabaseHas('purchase_order_discounts', [
                        'company_id' => $company->id,
                        'branch_id' => $branch->id,
                        'purchase_order_id' => $purchaseOrder->id,
                        'order' => $arr_product_unit_per_unit_sub_total_discount_order[$i][$j],
                        'discount_type' => $arr_product_unit_per_unit_sub_total_discount_discount_type[$i][$j],
                        'amount' => $arr_product_unit_per_unit_sub_total_discount_amount[$i][$j],
                    ]);
                }
            }
        }

        for ($i = 0; $i < count($arr_global_discount_discount_type); $i++) {
            $this->assertDatabaseHas('purchase_order_discounts', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_product_unit_id' => null,
                'order' => $arr_global_discount_order[$i],
                'discount_type' => $arr_global_discount_discount_type[$i],
                'amount' => $arr_global_discount_amount[$i],
            ]);
        }
    }

    public function test_purchase_order_api_call_create_with_too_high_per_unit_discount_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
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

        $productSeedCount = 10;
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
            $primaryUnitorder = random_int(0, $productUnitCount - 1);

            for ($j = 0; $j < $productUnitCount; $j++) {
                $product = $product->has(
                    ProductUnit::factory()
                        ->for($company)->for($units[$j])
                        ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                        ->setIsPrimaryUnit($j == $primaryUnitorder)
                );
            }

            $product->create();
        }

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_global_discount_id = [];
        $arr_global_discount_ulid = [];
        $arr_global_discount_order = [];
        $arr_global_discount_discount_type = [];
        $arr_global_discount_amount = [];
        $arr_product_unit_product_id = [];
        $arr_product_unit_product_unit_id = [];
        $arr_product_unit_qty = [];
        $arr_product_unit_amount_per_unit = [];
        $arr_product_unit_initial_price = [];
        $arr_product_unit_per_unit_discount_id = [];
        $arr_product_unit_per_unit_discount_ulid = [];
        $arr_product_unit_per_unit_discount_order = [];
        $arr_product_unit_per_unit_discount_discount_type = [];
        $arr_product_unit_per_unit_discount_amount = [];
        $arr_product_unit_per_unit_sub_total_discount_id = [];
        $arr_product_unit_per_unit_sub_total_discount_ulid = [];
        $arr_product_unit_per_unit_sub_total_discount_order = [];
        $arr_product_unit_per_unit_sub_total_discount_discount_type = [];
        $arr_product_unit_per_unit_sub_total_discount_amount = [];
        $arr_product_unit_vat_status = [];
        $arr_product_unit_vat_rate = [];
        $arr_product_unit_remarks = [];

        $ProductUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($ProductUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrderProductUnit = PurchaseOrderProductUnit::factory()->make();
            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_ulid, '');
            array_push($arr_product_unit_product_id, Hashids::encode($productUnit->product_id));
            array_push($arr_product_unit_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_qty, $purchaseOrderProductUnit['qty']);
            array_push($arr_product_unit_amount_per_unit, $purchaseOrderProductUnit->product_unit_amount_per_unit);
            array_push($arr_product_unit_initial_price, $purchaseOrderProductUnit->product_unit_initial_price);
            array_push($arr_product_unit_per_unit_discount_id, []);
            array_push($arr_product_unit_per_unit_discount_ulid, []);
            array_push($arr_product_unit_per_unit_discount_order, []);
            array_push($arr_product_unit_per_unit_discount_discount_type, []);
            array_push($arr_product_unit_per_unit_discount_amount, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_id, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_ulid, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_order, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_discount_type, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_amount, []);
            array_push($arr_product_unit_vat_status, $purchaseOrderProductUnit->vat_status);
            array_push($arr_product_unit_vat_rate, $purchaseOrderProductUnit->vat_rate);
            array_push($arr_product_unit_remarks, $purchaseOrderProductUnit->remarks);
        }

        for ($i = 0; $i < count($arr_product_unit_product_unit_id); $i++) {
            if ($i == 0) {
                $purchasePerUnitDiscount = PurchaseOrderDiscount::factory()->setPerUnitDiscountNominal()->make();
                array_push($arr_product_unit_per_unit_discount_id[$i], '');
                array_push($arr_product_unit_per_unit_discount_ulid[$i], '');
                array_push($arr_product_unit_per_unit_discount_order[$i], count($arr_product_unit_per_unit_discount_order[$i]));
                array_push($arr_product_unit_per_unit_discount_discount_type[$i], $purchasePerUnitDiscount->discount_type);
                array_push($arr_product_unit_per_unit_discount_amount[$i], $arr_product_unit_initial_price[$i] + 1);
            } else {
                if (random_int(0, 1)) {
                    $purchasePerUnitDiscount = PurchaseOrderDiscount::factory()->setPerUnitDiscountNominal()->make();
                    array_push($arr_product_unit_per_unit_discount_id[$i], '');
                    array_push($arr_product_unit_per_unit_discount_ulid[$i], '');
                    array_push($arr_product_unit_per_unit_discount_order[$i], count($arr_product_unit_per_unit_discount_order[$i]));
                    array_push($arr_product_unit_per_unit_discount_discount_type[$i], $purchasePerUnitDiscount->discount_type);
                    array_push($arr_product_unit_per_unit_discount_amount[$i], $arr_product_unit_initial_price[$i] + 1);
                }
            }
        }

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'supplier_id' => Hashids::encode($supplier->id),
            'arr_global_discount_id' => $arr_global_discount_id,
            'arr_global_discount_ulid' => $arr_global_discount_ulid,
            'arr_global_discount_order' => $arr_global_discount_order,
            'arr_global_discount_discount_type' => $arr_global_discount_discount_type,
            'arr_global_discount_amount' => $arr_global_discount_amount,
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_product_id' => $arr_product_unit_product_id,
            'arr_product_unit_product_unit_id' => $arr_product_unit_product_unit_id,
            'arr_product_unit_qty' => $arr_product_unit_qty,
            'arr_product_unit_amount_per_unit' => $arr_product_unit_amount_per_unit,
            'arr_product_unit_initial_price' => $arr_product_unit_initial_price,
            'arr_product_unit_per_unit_discount_id' => $arr_product_unit_per_unit_discount_id,
            'arr_product_unit_per_unit_discount_ulid' => $arr_product_unit_per_unit_discount_ulid,
            'arr_product_unit_per_unit_discount_order' => $arr_product_unit_per_unit_discount_order,
            'arr_product_unit_per_unit_discount_discount_type' => $arr_product_unit_per_unit_discount_discount_type,
            'arr_product_unit_per_unit_discount_amount' => $arr_product_unit_per_unit_discount_amount,
            'arr_product_unit_per_unit_sub_total_discount_id' => $arr_product_unit_per_unit_sub_total_discount_id,
            'arr_product_unit_per_unit_sub_total_discount_ulid' => $arr_product_unit_per_unit_sub_total_discount_ulid,
            'arr_product_unit_per_unit_sub_total_discount_order' => $arr_product_unit_per_unit_sub_total_discount_order,
            'arr_product_unit_per_unit_sub_total_discount_discount_type' => $arr_product_unit_per_unit_sub_total_discount_discount_type,
            'arr_product_unit_per_unit_sub_total_discount_amount' => $arr_product_unit_per_unit_sub_total_discount_amount,
            'arr_product_unit_vat_status' => $arr_product_unit_vat_status,
            'arr_product_unit_vat_rate' => $arr_product_unit_vat_rate,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.save'), $purchaseOrderArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_purchase_order_api_call_create_with_too_high_per_unit_subtotal_discount_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
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

        $productSeedCount = 10;
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
            $primaryUnitorder = random_int(0, $productUnitCount - 1);

            for ($j = 0; $j < $productUnitCount; $j++) {
                $product = $product->has(
                    ProductUnit::factory()
                        ->for($company)->for($units[$j])
                        ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                        ->setIsPrimaryUnit($j == $primaryUnitorder)
                );
            }

            $product->create();
        }

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_global_discount_id = [];
        $arr_global_discount_ulid = [];
        $arr_global_discount_order = [];
        $arr_global_discount_discount_type = [];
        $arr_global_discount_amount = [];
        $arr_product_unit_product_id = [];
        $arr_product_unit_product_unit_id = [];
        $arr_product_unit_qty = [];
        $arr_product_unit_amount_per_unit = [];
        $arr_product_unit_initial_price = [];
        $arr_product_unit_per_unit_discount_id = [];
        $arr_product_unit_per_unit_discount_ulid = [];
        $arr_product_unit_per_unit_discount_order = [];
        $arr_product_unit_per_unit_discount_discount_type = [];
        $arr_product_unit_per_unit_discount_amount = [];
        $arr_product_unit_per_unit_sub_total_discount_id = [];
        $arr_product_unit_per_unit_sub_total_discount_ulid = [];
        $arr_product_unit_per_unit_sub_total_discount_order = [];
        $arr_product_unit_per_unit_sub_total_discount_discount_type = [];
        $arr_product_unit_per_unit_sub_total_discount_amount = [];
        $arr_product_unit_vat_status = [];
        $arr_product_unit_vat_rate = [];
        $arr_product_unit_remarks = [];

        $ProductUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($ProductUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrderProductUnit = PurchaseOrderProductUnit::factory()->make();
            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_ulid, '');
            array_push($arr_product_unit_product_id, Hashids::encode($productUnit->product_id));
            array_push($arr_product_unit_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_qty, $purchaseOrderProductUnit['qty']);
            array_push($arr_product_unit_amount_per_unit, $purchaseOrderProductUnit->product_unit_amount_per_unit);
            array_push($arr_product_unit_initial_price, $purchaseOrderProductUnit->product_unit_initial_price);
            array_push($arr_product_unit_per_unit_discount_id, []);
            array_push($arr_product_unit_per_unit_discount_ulid, []);
            array_push($arr_product_unit_per_unit_discount_order, []);
            array_push($arr_product_unit_per_unit_discount_discount_type, []);
            array_push($arr_product_unit_per_unit_discount_amount, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_id, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_ulid, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_order, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_discount_type, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_amount, []);
            array_push($arr_product_unit_vat_status, $purchaseOrderProductUnit->vat_status);
            array_push($arr_product_unit_vat_rate, $purchaseOrderProductUnit->vat_rate);
            array_push($arr_product_unit_remarks, $purchaseOrderProductUnit->remarks);
        }

        $purchaseOrderActions = new PurchaseOrderActions();
        for ($i = 0; $i < count($arr_product_unit_product_unit_id); $i++) {
            for ($j = 0; $j < 3; $j++) {
                if (random_int(0, 1)) {
                    $maxValue = $arr_product_unit_initial_price[$i];

                    if (count($arr_product_unit_per_unit_discount_order[$i])) {
                        $maxValue = $maxValue - $purchaseOrderActions->calculatePerUnitDiscountFromFreeArray(
                            $arr_product_unit_initial_price[$i],
                            $arr_product_unit_per_unit_discount_order[$i],
                            $arr_product_unit_per_unit_discount_discount_type[$i],
                            $arr_product_unit_per_unit_discount_amount[$i],
                        );
                    }

                    $purchasePerUnitDiscount = PurchaseOrderDiscount::factory()->setPerUnitDiscountRandom($maxValue)->make();
                    array_push($arr_product_unit_per_unit_discount_id[$i], '');
                    array_push($arr_product_unit_per_unit_discount_ulid[$i], '');
                    array_push($arr_product_unit_per_unit_discount_order[$i], count($arr_product_unit_per_unit_discount_order[$i]));
                    array_push($arr_product_unit_per_unit_discount_discount_type[$i], $purchasePerUnitDiscount->discount_type);
                    array_push($arr_product_unit_per_unit_discount_amount[$i], $purchasePerUnitDiscount->amount);
                }
            }

            $qty = $arr_product_unit_qty[$i];

            $initialPrice = $arr_product_unit_initial_price[$i];

            $perUnitDiscount = 0;
            if (count($arr_product_unit_per_unit_discount_order[$i])) {
                $perUnitDiscount = $purchaseOrderActions->calculatePerUnitDiscountFromFreeArray(
                    $arr_product_unit_initial_price[$i],
                    $arr_product_unit_per_unit_discount_order[$i],
                    $arr_product_unit_per_unit_discount_discount_type[$i],
                    $arr_product_unit_per_unit_discount_amount[$i],
                );
            }

            $priceAfterDisc = $initialPrice - $perUnitDiscount;

            $subTotal = $qty * $priceAfterDisc;

            if ($i == 0) {
                $purchasePerUnitSubTotalDiscount = PurchaseOrderDiscount::factory()->setPerUnitSubTotalDiscountNominal()->make();
                array_push($arr_product_unit_per_unit_sub_total_discount_id[$i], '');
                array_push($arr_product_unit_per_unit_sub_total_discount_ulid[$i], '');
                array_push($arr_product_unit_per_unit_sub_total_discount_order[$i], count($arr_product_unit_per_unit_sub_total_discount_order[$i]));
                array_push($arr_product_unit_per_unit_sub_total_discount_discount_type[$i], $purchasePerUnitSubTotalDiscount->discount_type);
                array_push($arr_product_unit_per_unit_sub_total_discount_amount[$i], $subTotal + 1);
            } else {
                if (random_int(0, 1)) {
                    $purchasePerUnitSubTotalDiscount = PurchaseOrderDiscount::factory()->setPerUnitSubTotalDiscountNominal()->make();
                    array_push($arr_product_unit_per_unit_sub_total_discount_id[$i], '');
                    array_push($arr_product_unit_per_unit_sub_total_discount_ulid[$i], '');
                    array_push($arr_product_unit_per_unit_sub_total_discount_order[$i], count($arr_product_unit_per_unit_sub_total_discount_order[$i]));
                    array_push($arr_product_unit_per_unit_sub_total_discount_discount_type[$i], $purchasePerUnitSubTotalDiscount->discount_type);
                    array_push($arr_product_unit_per_unit_sub_total_discount_amount[$i], $subTotal + 1);
                }
            }
        }

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'supplier_id' => Hashids::encode($supplier->id),
            'arr_global_discount_id' => $arr_global_discount_id,
            'arr_global_discount_ulid' => $arr_global_discount_ulid,
            'arr_global_discount_order' => $arr_global_discount_order,
            'arr_global_discount_discount_type' => $arr_global_discount_discount_type,
            'arr_global_discount_amount' => $arr_global_discount_amount,
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_product_id' => $arr_product_unit_product_id,
            'arr_product_unit_product_unit_id' => $arr_product_unit_product_unit_id,
            'arr_product_unit_qty' => $arr_product_unit_qty,
            'arr_product_unit_amount_per_unit' => $arr_product_unit_amount_per_unit,
            'arr_product_unit_initial_price' => $arr_product_unit_initial_price,
            'arr_product_unit_per_unit_discount_id' => $arr_product_unit_per_unit_discount_id,
            'arr_product_unit_per_unit_discount_ulid' => $arr_product_unit_per_unit_discount_ulid,
            'arr_product_unit_per_unit_discount_order' => $arr_product_unit_per_unit_discount_order,
            'arr_product_unit_per_unit_discount_discount_type' => $arr_product_unit_per_unit_discount_discount_type,
            'arr_product_unit_per_unit_discount_amount' => $arr_product_unit_per_unit_discount_amount,
            'arr_product_unit_per_unit_sub_total_discount_id' => $arr_product_unit_per_unit_sub_total_discount_id,
            'arr_product_unit_per_unit_sub_total_discount_ulid' => $arr_product_unit_per_unit_sub_total_discount_ulid,
            'arr_product_unit_per_unit_sub_total_discount_order' => $arr_product_unit_per_unit_sub_total_discount_order,
            'arr_product_unit_per_unit_sub_total_discount_discount_type' => $arr_product_unit_per_unit_sub_total_discount_discount_type,
            'arr_product_unit_per_unit_sub_total_discount_amount' => $arr_product_unit_per_unit_sub_total_discount_amount,
            'arr_product_unit_vat_status' => $arr_product_unit_vat_status,
            'arr_product_unit_vat_rate' => $arr_product_unit_vat_rate,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ])->toArray();
        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.save'), $purchaseOrderArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_purchase_order_api_call_create_with_too_high_global_discount_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
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

        $productSeedCount = 10;
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
            $primaryUnitorder = random_int(0, $productUnitCount - 1);

            for ($j = 0; $j < $productUnitCount; $j++) {
                $product = $product->has(
                    ProductUnit::factory()
                        ->for($company)->for($units[$j])
                        ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                        ->setIsPrimaryUnit($j == $primaryUnitorder)
                );
            }

            $product->create();
        }

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_global_discount_id = [];
        $arr_global_discount_ulid = [];
        $arr_global_discount_order = [];
        $arr_global_discount_discount_type = [];
        $arr_global_discount_amount = [];
        $arr_product_unit_product_id = [];
        $arr_product_unit_product_unit_id = [];
        $arr_product_unit_qty = [];
        $arr_product_unit_amount_per_unit = [];
        $arr_product_unit_initial_price = [];
        $arr_product_unit_per_unit_discount_id = [];
        $arr_product_unit_per_unit_discount_ulid = [];
        $arr_product_unit_per_unit_discount_order = [];
        $arr_product_unit_per_unit_discount_discount_type = [];
        $arr_product_unit_per_unit_discount_amount = [];
        $arr_product_unit_per_unit_sub_total_discount_id = [];
        $arr_product_unit_per_unit_sub_total_discount_ulid = [];
        $arr_product_unit_per_unit_sub_total_discount_order = [];
        $arr_product_unit_per_unit_sub_total_discount_discount_type = [];
        $arr_product_unit_per_unit_sub_total_discount_amount = [];
        $arr_product_unit_vat_status = [];
        $arr_product_unit_vat_rate = [];
        $arr_product_unit_remarks = [];

        $ProductUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($ProductUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrderProductUnit = PurchaseOrderProductUnit::factory()->make();
            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_ulid, '');
            array_push($arr_product_unit_product_id, Hashids::encode($productUnit->product_id));
            array_push($arr_product_unit_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_qty, $purchaseOrderProductUnit['qty']);
            array_push($arr_product_unit_amount_per_unit, $purchaseOrderProductUnit->product_unit_amount_per_unit);
            array_push($arr_product_unit_initial_price, $purchaseOrderProductUnit->product_unit_initial_price);
            array_push($arr_product_unit_per_unit_discount_id, []);
            array_push($arr_product_unit_per_unit_discount_ulid, []);
            array_push($arr_product_unit_per_unit_discount_order, []);
            array_push($arr_product_unit_per_unit_discount_discount_type, []);
            array_push($arr_product_unit_per_unit_discount_amount, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_id, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_ulid, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_order, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_discount_type, []);
            array_push($arr_product_unit_per_unit_sub_total_discount_amount, []);
            array_push($arr_product_unit_vat_status, $purchaseOrderProductUnit->vat_status);
            array_push($arr_product_unit_vat_rate, $purchaseOrderProductUnit->vat_rate);
            array_push($arr_product_unit_remarks, $purchaseOrderProductUnit->remarks);
        }

        $purchaseOrderActions = new PurchaseOrderActions();
        for ($i = 0; $i < count($arr_product_unit_product_unit_id); $i++) {
            for ($j = 0; $j < 3; $j++) {
                if (random_int(0, 1)) {
                    $maxValue = $arr_product_unit_initial_price[$i];

                    if (count($arr_product_unit_per_unit_discount_order[$i])) {
                        $maxValue = $maxValue - $purchaseOrderActions->calculatePerUnitDiscountFromFreeArray(
                            $arr_product_unit_initial_price[$i],
                            $arr_product_unit_per_unit_discount_order[$i],
                            $arr_product_unit_per_unit_discount_discount_type[$i],
                            $arr_product_unit_per_unit_discount_amount[$i],
                        );
                    }

                    $purchasePerUnitDiscount = PurchaseOrderDiscount::factory()->setPerUnitDiscountRandom($maxValue)->make();
                    array_push($arr_product_unit_per_unit_discount_id[$i], '');
                    array_push($arr_product_unit_per_unit_discount_ulid[$i], '');
                    array_push($arr_product_unit_per_unit_discount_order[$i], count($arr_product_unit_per_unit_discount_order[$i]));
                    array_push($arr_product_unit_per_unit_discount_discount_type[$i], $purchasePerUnitDiscount->discount_type);
                    array_push($arr_product_unit_per_unit_discount_amount[$i], $purchasePerUnitDiscount->amount);
                }
            }

            for ($j = 0; $j < 3; $j++) {
                if (random_int(0, 1)) {
                    $qty = $arr_product_unit_qty[$i];

                    $initialPrice = $arr_product_unit_initial_price[$i];

                    $perUnitDiscount = 0;
                    if (count($arr_product_unit_per_unit_discount_order[$i])) {
                        $perUnitDiscount = $purchaseOrderActions->calculatePerUnitDiscountFromFreeArray(
                            $arr_product_unit_initial_price[$i],
                            $arr_product_unit_per_unit_discount_order[$i],
                            $arr_product_unit_per_unit_discount_discount_type[$i],
                            $arr_product_unit_per_unit_discount_amount[$i],
                        );
                    }

                    $priceAfterDisc = $initialPrice - $perUnitDiscount;

                    $subTotal = $qty * $priceAfterDisc;

                    $maxValue = $subTotal;
                    if (count($arr_product_unit_per_unit_sub_total_discount_order[$i])) {
                        $maxValue = $maxValue - $purchaseOrderActions->calculatePerUnitSubTotalDiscountFromFreeArray(
                            $subTotal,
                            $arr_product_unit_per_unit_sub_total_discount_order[$i],
                            $arr_product_unit_per_unit_sub_total_discount_discount_type[$i],
                            $arr_product_unit_per_unit_sub_total_discount_amount[$i]
                        );
                    }

                    $purchasePerUnitSubTotalDiscount = PurchaseOrderDiscount::factory()->setPerUnitSubTotalDiscountRandom($maxValue)->make();
                    array_push($arr_product_unit_per_unit_sub_total_discount_id[$i], '');
                    array_push($arr_product_unit_per_unit_sub_total_discount_ulid[$i], '');
                    array_push($arr_product_unit_per_unit_sub_total_discount_order[$i], count($arr_product_unit_per_unit_sub_total_discount_order[$i]));
                    array_push($arr_product_unit_per_unit_sub_total_discount_discount_type[$i], $purchasePerUnitSubTotalDiscount->discount_type);
                    array_push($arr_product_unit_per_unit_sub_total_discount_amount[$i], $purchasePerUnitSubTotalDiscount->amount);
                }
            }
        }

        $arrProductUnit = [];
        for ($i = 0; $i < count($arr_product_unit_product_unit_id); $i++) {
            $qty = $arr_product_unit_qty[$i];

            $initialPrice = $arr_product_unit_initial_price[$i];

            $perUnitDiscount = $purchaseOrderActions->calculatePerUnitDiscountFromFreeArray(
                $arr_product_unit_initial_price[$i],
                $arr_product_unit_per_unit_discount_order[$i],
                $arr_product_unit_per_unit_discount_discount_type[$i],
                $arr_product_unit_per_unit_discount_amount[$i]
            );

            $priceAfterDisc = $initialPrice - $perUnitDiscount;

            $subTotal = $qty * $priceAfterDisc;

            $perUnitSubTotalDiscount = $purchaseOrderActions->calculatePerUnitSubTotalDiscountFromFreeArray(
                $subTotal,
                $arr_product_unit_per_unit_sub_total_discount_order[$i],
                $arr_product_unit_per_unit_sub_total_discount_discount_type[$i],
                $arr_product_unit_per_unit_sub_total_discount_amount[$i]
            );

            $total = $subTotal - $perUnitSubTotalDiscount;

            array_push($arrProductUnit, [
                'qty' => $qty,
                'initial_price' => $initialPrice,
                'per_unit_disc' => $perUnitDiscount,
                'price_after_disc' => $priceAfterDisc,
                'sub_total' => $subTotal,
                'per_unit_sub_total_discount' => $perUnitSubTotalDiscount,
                'total' => $total,
                'global_disc' => 0,
                'grand_total' => 0,
                'final_price' => 0,
            ]);
        }

        $grandTotal = 0;
        foreach ($arrProductUnit as $productUnit) {
            $grandTotal = $grandTotal + $productUnit['total'];
        }

        if (random_int(0, 1)) {
            $purchaseOrderDiscount = PurchaseOrderDiscount::factory()->setGlobalDiscountRandom($maxValue)->make();
            array_push($arr_global_discount_id, '');
            array_push($arr_global_discount_ulid, '');
            array_push($arr_global_discount_order, count($arr_global_discount_order));
            array_push($arr_global_discount_discount_type, $purchaseOrderDiscount->discount_type);
            array_push($arr_global_discount_amount, $grandTotal + 1);
        } else {
            $purchaseOrderDiscount = PurchaseOrderDiscount::factory()->setGlobalDiscountRandom($maxValue)->make();
            array_push($arr_global_discount_id, '');
            array_push($arr_global_discount_ulid, '');
            array_push($arr_global_discount_order, count($arr_global_discount_order));
            array_push($arr_global_discount_discount_type, $purchaseOrderDiscount->discount_type);
            array_push($arr_global_discount_amount, $grandTotal + 1);
        }

        $globalDiscount = $purchaseOrderActions->calculateGlobalDiscountFromFreeArray(
            $grandTotal,
            $arr_global_discount_order,
            $arr_global_discount_discount_type,
            $arr_global_discount_amount,
        );

        foreach ($arrProductUnit as $j => $productUnit) {
            $arrProductUnit[$j]['global_disc'] = $productUnit['total'] / $grandTotal * $globalDiscount;
            $arrProductUnit[$j]['grand_total'] = $productUnit['total'] - $arrProductUnit[$j]['global_disc'];
            $arrProductUnit[$j]['final_price'] = $arrProductUnit[$j]['grand_total'] / $productUnit['qty'];
        }

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'supplier_id' => Hashids::encode($supplier->id),
            'arr_global_discount_id' => $arr_global_discount_id,
            'arr_global_discount_ulid' => $arr_global_discount_ulid,
            'arr_global_discount_order' => $arr_global_discount_order,
            'arr_global_discount_discount_type' => $arr_global_discount_discount_type,
            'arr_global_discount_amount' => $arr_global_discount_amount,
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_product_id' => $arr_product_unit_product_id,
            'arr_product_unit_product_unit_id' => $arr_product_unit_product_unit_id,
            'arr_product_unit_qty' => $arr_product_unit_qty,
            'arr_product_unit_amount_per_unit' => $arr_product_unit_amount_per_unit,
            'arr_product_unit_initial_price' => $arr_product_unit_initial_price,
            'arr_product_unit_per_unit_discount_id' => $arr_product_unit_per_unit_discount_id,
            'arr_product_unit_per_unit_discount_ulid' => $arr_product_unit_per_unit_discount_ulid,
            'arr_product_unit_per_unit_discount_order' => $arr_product_unit_per_unit_discount_order,
            'arr_product_unit_per_unit_discount_discount_type' => $arr_product_unit_per_unit_discount_discount_type,
            'arr_product_unit_per_unit_discount_amount' => $arr_product_unit_per_unit_discount_amount,
            'arr_product_unit_per_unit_sub_total_discount_id' => $arr_product_unit_per_unit_sub_total_discount_id,
            'arr_product_unit_per_unit_sub_total_discount_ulid' => $arr_product_unit_per_unit_sub_total_discount_ulid,
            'arr_product_unit_per_unit_sub_total_discount_order' => $arr_product_unit_per_unit_sub_total_discount_order,
            'arr_product_unit_per_unit_sub_total_discount_discount_type' => $arr_product_unit_per_unit_sub_total_discount_discount_type,
            'arr_product_unit_per_unit_sub_total_discount_amount' => $arr_product_unit_per_unit_sub_total_discount_amount,
            'arr_product_unit_vat_status' => $arr_product_unit_vat_status,
            'arr_product_unit_vat_rate' => $arr_product_unit_vat_rate,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.save'), $purchaseOrderArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
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
