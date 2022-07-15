<?php

namespace Tests\Feature\Service;

use App\Enums\ProductGroupCategory;
use Exception;
use App\Models\User;
use App\Models\Company;
use App\Models\ProductGroup;
use Tests\ServiceTestCase;
use App\Services\ProductGroupService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class ProductGroupServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupService = app(ProductGroupService::class);
    }

    #region create
    public function test_productgroup_service_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();
        
        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => $user->companies->first()->id
        ]);

        $result = $this->productGroupService->create($productGroupArr->toArray());

        $this->assertDatabaseHas('product_groups', [
            'id' => $result->id,
            'company_id' => $productGroupArr['company_id'],
            'code' => $productGroupArr['code'],
            'name' => $productGroupArr['name'],
            'category' => $productGroupArr['category'],
        ]);
    }

    public function test_productgroup_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->productGroupService->create([]);
    }

    #endregion

    #region list

    public function test_productgroup_service_call_list_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(20), 'productGroups'), 'companies')
                    ->create();

        $productGroupCategory = ProductGroupCategory::PRODUCTS_AND_SERVICES->value;

        $result = $this->productGroupService->list(
            companyId: $user->companies->first()->id,
            productGroupCategory: $productGroupCategory,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_productgroup_service_call_list_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(20), 'productGroups'), 'companies')
                    ->create();

        $productGroupCategory = ProductGroupCategory::PRODUCTS_AND_SERVICES->value;

        $result = $this->productGroupService->list(
            companyId: $user->companies->first()->id,
            productGroupCategory: $productGroupCategory,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_productgroup_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;
        
        $productGroupCategory = ProductGroupCategory::PRODUCTS_AND_SERVICES->value;

        $result = $this->productGroupService->list(
            companyId: $maxId,
            productGroupCategory: $productGroupCategory,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_productgroup_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        ProductGroup::factory()->insertStringInName('testing')->count(10)->create([
            'company_id' => $companyId
        ]);

        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
        ]);
        
        $productGroupCategory = ProductGroupCategory::PRODUCTS_AND_SERVICES->value;

        $result = $this->productGroupService->list(
            companyId: $companyId, 
            productGroupCategory: $productGroupCategory,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_productgroup_service_call_list_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);
        
        $productGroupCategory = ProductGroupCategory::PRODUCTS_AND_SERVICES->value;

        $result = $this->productGroupService->list(
            companyId: $companyId, 
            productGroupCategory: $productGroupCategory,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() > 1);
    }

    public function test_productgroup_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);
        
        $productGroupCategory = ProductGroupCategory::PRODUCTS_AND_SERVICES->value;

        $result = $this->productGroupService->list(
            companyId: $companyId, 
            productGroupCategory: $productGroupCategory,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() > 1);
    }

    #endregion

    #region read

    public function test_productgroup_service_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(ProductGroup::factory()->count(20), 'productGroups'), 'companies')
                    ->create();
        
        $productGroup = $user->companies->first()->productGroups()->inRandomOrder()->first();

        $result = $this->productGroupService->read($productGroup);

        $this->assertInstanceOf(ProductGroup::class, $result);
    }

    #endregion

    #region update

    public function test_productgroup_service_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(ProductGroup::factory(), 'productGroups'), 'companies')
                    ->create();

        $productGroup = $user->companies->first()->productGroups->first();
        $productGroupArr = ProductGroup::factory()->make();

        $result = $this->productGroupService->update($productGroup, $productGroupArr->toArray());
        
        $this->assertInstanceOf(ProductGroup::class, $result);
        $this->assertDatabaseHas('product_groups', [
            'id' => $productGroup->id,
            'company_id' => $productGroup->company_id,
            'code' => $productGroupArr['code'],
            'name' => $productGroupArr['name'],
        ]);
    }

    public function test_productgroup_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(ProductGroup::factory(), 'product_groups'), 'companies')
                    ->create();

        $productGroup = $user->companies->first()->product_groups->first();
        $productGroupArr = [];
            
        $this->productGroupService->update($productGroup, $productGroupArr);
    }

    #endregion

    #region delete

    public function test_productgroup_service_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(ProductGroup::factory()->count(5), 'productGroups'), 'companies')
                    ->create();

        $productGroup = $user->companies->first()->productGroups->first();
            
        $result = $this->productGroupService->delete($productGroup);
        
        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('product_groups', [
            'id' => $productGroup->id
        ]);
    }

    #endregion

    #region others

    #endregion
}