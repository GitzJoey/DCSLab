<?php

namespace Tests\Feature\Service;

use Exception;
use App\Models\User;
use App\Models\Brand;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\BrandService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Factories\Sequence;

class BrandServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandService = app(BrandService::class);
    }

    /* #region create */
    public function test_brand_service_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();
        
        $brandArr = Brand::factory()->make([
            'company_id' => $user->companies->first()->id
        ])->toArray();
        
        $result = $this->brandService->create($brandArr);

        $this->assertDatabaseHas('brands', [
            'id' => $result->id,
            'company_id' => $brandArr['company_id'],
            'code' => $brandArr['code'],
            'name' => $brandArr['name'],
        ]);
    }

    public function test_brand_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->brandService->create([]);
    }
    /* #endregion */

    /* #region list */
    public function test_brand_service_call_list_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Brand::factory()->count(20), 'brands'), 'companies')
                    ->create();

        $result = $this->brandService->list(
            companyId: $user->companies->first()->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_brand_service_call_list_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Brand::factory()->count(20), 'brands'), 'companies')
                    ->create();

        $result = $this->brandService->list(
            companyId: $user->companies->first()->id,
            search: '',
            paginate: false,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_brand_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;
        $result = $this->brandService->list(companyId: $maxId, search: '', paginate: false);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_brand_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        Brand::factory()->insertStringInName('testing')->count(25)->create([
            'company_id' => $companyId
        ]);

        Brand::factory()->count(10)->create([
            'company_id' => $companyId,
        ]);

        $result = $this->brandService->list(
            companyId: $companyId,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 25);
    }

    public function test_brand_service_call_list_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        Brand::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);

        $result = $this->brandService->list(
            companyId: $companyId, 
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 25);
    }

    public function test_brand_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        Brand::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);

        $result = $this->brandService->list(
            companyId: $companyId, 
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() > 1);
    }
    /* #endregion */

    /* #region read */
    public function test_brand_service_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Brand::factory()->count(20), 'brands'), 'companies')
                    ->create();
        $brand = $user->companies->first()->brands()->inRandomOrder()->first();

        $result = $this->brandService->read($brand);

        $this->assertInstanceOf(Brand::class, $result);
    }
    /* #endregion */

    /* #region update */
    public function test_brand_service_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Brand::factory(), 'brands'), 'companies')
                    ->create();
                    
        $brand = $user->companies->first()->brands->first();
        $brandArr = Brand::factory()->make()->toArray();

        $result = $this->brandService->update($brand, $brandArr);
        
        $this->assertInstanceOf(Brand::class, $result);
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'company_id' => $brand->company_id,
            'code' => $brandArr['code'],
            'name' => $brandArr['name'],
        ]);
    }

    public function test_brand_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Brand::factory(), 'brands'), 'companies')
                    ->create();

        $brand = $user->companies->first()->brands->first();
        $brandArr = [];
        
        $this->brandService->update($brand, $brandArr);
    }
    /* #endregion */

    /* #region delete */
    public function test_brand_service_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Brand::factory()->count(5), 'brands'), 'companies')
                    ->create();

        $brand = $user->companies->first()->brands->first();
        
        $result = $this->brandService->delete($brand);
        
        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('brands', [
            'id' => $brand->id
        ]);
    }
    /* #endregion */

    /* #region others */
    public function test_brand_service_call_function_generate_unique_code_expect_unique_code_returned()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Brand::factory()->count(5), 'brands'), 'companies')
                    ->create();

        $code = $this->brandService->generateUniqueCode();

        $this->assertIsString($code);
        
        $resultCount = $user->companies()->first()->brands()->where('code', '=', $code)->count();
        $this->assertTrue($resultCount == 0);
    }

    public function test_brand_service_call_function_is_unique_code_expect_can_detect_unique_code()
    {
        $user = User::factory()
                    ->has(Company::factory()->count(2)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        Brand::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1',
        ]);

        Brand::factory()->create([
            'company_id' => $companyId_2,
            'code' => 'test2',
        ]);

        $this->assertFalse($this->brandService->isUniqueCode('test1', $companyId_1));
        $this->assertTrue($this->brandService->isUniqueCode('test2', $companyId_1));
        $this->assertTrue($this->brandService->isUniqueCode('test3', $companyId_1));
        $this->assertTrue($this->brandService->isUniqueCode('test1', $companyId_2));
    }
    /* #endregion */
}