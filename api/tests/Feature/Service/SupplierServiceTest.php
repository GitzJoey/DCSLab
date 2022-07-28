<?php

namespace Tests\Feature\Service;

use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Supplier;
use Tests\ServiceTestCase;
use App\Enums\RecordStatus;
use App\Actions\RandomGenerator;
use App\Services\SupplierService;
use Database\Seeders\UnitTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\ProductGroupTableSeeder;

class SupplierServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplierService = app(SupplierService::class);
        $this->randomGenerator = new RandomGenerator();
    }

    #region create
    public function test_supplier_service_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [$companyId]);

        do {
            $productSeeder = new ProductTableSeeder();
            $productSeeder->callWith(ProductTableSeeder::class, [20, $companyId]);

            $productCount = Product::where([
                ['company_id', '=', $companyId],
                ['brand_id', '!=', null]
            ])->count();
        } while ($productCount == 0);
        
        $supplierArr = Supplier::factory()->make([
            'company_id' => $user->companies->first()->id
        ])->toArray();
        
        $picArr = Profile::factory()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'] . $picArr['last_name']) . $this->randomGenerator->generateNumber(1, 999);
        $picArr['email'] = $picArr['name'] . '@something.com';
        $picArr['contact'] = $supplierArr['contact'];
        $picArr['address'] = $supplierArr['address'];
        $picArr['city'] = $supplierArr['city'];
        $picArr['tax_id'] = $supplierArr['tax_id'];

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $productCount);
        $productIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productsArr = [];
        foreach ($productIds as $productId) {
            $supplierProduct = [];
            $supplierProduct['product_id'] = $productId;
            $supplierProduct['main_product'] = $this->randomGenerator->generateNumber(0, 1);

            array_push($productsArr, $supplierProduct);
        }

        $result = $this->supplierService->create(
            supplierArr: $supplierArr,
            picArr: $picArr,
            productsArr: $productsArr
        );

        $this->assertDatabaseHas('suppliers', [
            'id' => $result->id,
            'company_id' => $companyId,
            'code' => $result['code'],
            'name' => $result['name'],
            'contact' => $result['contact'],
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
                'company_id' => $companyId,
                'supplier_id' => $result->id,
                'product_id' => $product['product_id'],
                'main_product' => $product['main_product'],
            ]);
        }
    }

    public function test_supplier_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region list

    public function test_supplier_service_call_list_with_paginate_true_expect_Paginator_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_supplier_service_call_list_with_paginate_false_expect_Collection_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_supplier_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_supplier_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_supplier_service_call_list_with_page_parameter_negative_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_supplier_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region read

    public function test_supplier_service_call_read_expect_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region update

    public function test_supplier_service_call_update_expect_db_updated()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_supplier_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region delete

    public function test_supplier_service_call_delete_expect_bool()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region others

    #endregion
}
