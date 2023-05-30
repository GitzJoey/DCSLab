<?php

namespace Tests\Unit\Actions\SupplierActions;

use App\Actions\Supplier\SupplierActions;
use App\Enums\ProductGroupCategory;
use App\Enums\RecordStatus;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Profile;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SupplierActionsCreateTest extends ActionsTestCase
{
    private SupplierActions $supplierActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplierActions = new SupplierActions();
    }

    public function test_supplier_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
            $productGroup = ProductGroup::whereRelation('company', 'id', $company->id)
                ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                ->inRandomOrder()->first();

            $brand = Brand::whereRelation('company', 'id', $company->id)->inRandomOrder()->first();

            $product = Product::factory()
                ->for($company)
                ->for($productGroup)
                ->for($brand)
                ->setProductTypeAsProduct()
                ->setStatusActive()
                ->create();
        }

        $supplierArr = Supplier::factory()->for($company)->make()->toArray();

        $picArr = Profile::factory()->setStatusInactive()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).random_int(1, 5);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = '123456';
        $picArr['contact'] = $supplierArr['contact'];
        $picArr['address'] = $supplierArr['address'];
        $picArr['city'] = $supplierArr['city'];
        $picArr['tax_id'] = $supplierArr['tax_id'];

        $supplierProductsCount = random_int(0, $company->products()->count());
        $productIds = $company->products()->take($supplierProductsCount)->pluck('id');

        $productsArr = [];
        foreach ($productIds as $productId) {
            $supplierProduct = [];
            $supplierProduct['product_id'] = $productId;
            $supplierProduct['main_product'] = random_int(0, 1);

            array_push($productsArr, $supplierProduct);
        }

        $result = $this->supplierActions->create(
            supplierArr: $supplierArr,
            picArr: $picArr,
            productsArr: $productsArr
        );

        $this->assertDatabaseHas('suppliers', [
            'id' => $result->id,
            'company_id' => $company->id,
            'code' => $result['code'],
            'name' => $result['name'],
            'contact' => $result['contact'],
            'city' => $result['city'],
            'payment_term_type' => $result['payment_term_type'],
            'payment_term' => $result['payment_term'],
            'taxable_enterprise' => $result['taxable_enterprise'],
            'tax_id' => $result['tax_id'],
            'status' => $result['status'],
            'remarks' => $result['remarks'],
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => $picArr['first_name'],
            'last_name' => $picArr['last_name'],
            'status' => RecordStatus::ACTIVE->value,
        ]);

        foreach ($productsArr as $product) {
            $this->assertDatabaseHas('supplier_products', [
                'company_id' => $company->id,
                'supplier_id' => $result->id,
                'product_id' => $product['product_id'],
                'main_product' => $product['main_product'],
            ]);
        }
    }

    public function test_supplier_actions_call_create_user_pic_expect_success()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $companyId = $company->id;

        $supplierArr = Supplier::factory()->for($company)->make()->toArray();

        $picArr = Profile::factory()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).random_int(1, 5);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = '123456';
        $picArr['contact'] = $supplierArr['contact'];
        $picArr['address'] = $supplierArr['address'];
        $picArr['city'] = $supplierArr['city'];
        $picArr['tax_id'] = $supplierArr['tax_id'];

        for ($i = 0; $i < 3; $i++) {
            $productGroup = ProductGroup::whereRelation('company', 'id', $company->id)
                ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                ->inRandomOrder()->first();

            $brand = Brand::whereRelation('company', 'id', $company->id)->inRandomOrder()->first();

            $product = Product::factory()
                ->for($company)
                ->for($productGroup)
                ->for($brand)
                ->setProductTypeAsProduct()
                ->setStatusActive()
                ->create();
        }

        $supplierProductsCount = random_int(0, $company->products()->count());
        $productIds = $company->products()->take($supplierProductsCount)->pluck('id');

        $productsArr = [];
        foreach ($productIds as $productId) {
            $supplierProduct = [];
            $supplierProduct['product_id'] = $productId;
            $supplierProduct['main_product'] = random_int(0, 1);

            array_push($productsArr, $supplierProduct);
        }

        $result = $this->supplierActions->create(
            supplierArr: $supplierArr,
            picArr: $picArr,
            productsArr: $productsArr
        );

        $this->assertDatabaseHas('profiles', [
            'first_name' => $picArr['first_name'],
            'last_name' => $picArr['last_name'],
            'status' => RecordStatus::ACTIVE->value,
        ]);

        foreach ($productsArr as $product) {
            $this->assertDatabaseHas('supplier_products', [
                'company_id' => $companyId,
                'supplier_id' => $result->id,
                'product_id' => $product['product_id'],
                'main_product' => $product['main_product'],
            ]);
        }
    }

    public function test_supplier_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->supplierActions->create(
            [],
            [],
            []
        );
    }
}
