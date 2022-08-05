<?php

namespace Tests\Feature\Service;

use Exception;
use App\Models\User;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\CompanyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Factories\Sequence;

class CompanyServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyService = app(CompanyService::class);
    }

    /* #region create */
    public function test_company_service_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyArr = Company::factory()->make([
            'user_id' => $user->id
        ]);

        $result = $this->companyService->create($companyArr->toArray());

        $this->assertDatabaseHas('companies', [
            'id' => $result->id,
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
        ]);
    }

    public function test_company_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->companyService->create([]);
    }
    /* #endregion */

    /* #region list */
    public function test_company_service_call_list_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->companyService->list(
            userId: $user->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_company_service_call_list_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->companyService->list(
            userId: $user->id,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_company_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();
        
        $companies1 = Company::factory()->count(10)->create([
            'name' => $this->faker->name().' '.'testing'
        ]);
        for ($i = 0; $i < $companies1->count(); $i++) {
            $user->companies()->attach([$companies1[$i]->id]);
        }

        $companies2 = Company::factory()->count(10)->create([]);
        for ($i = 0; $i < $companies2->count(); $i++) {
            $user->companies()->attach([$companies2[$i]->id]);
        }

        $result = $this->companyService->list(
            userId: $user->id,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_company_service_call_list_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companies = Company::factory()->count(25)->create([]);
        for ($i = 0; $i < $companies->count(); $i++) {
            $user->companies()->attach([$companies[$i]->id]);
        }

        $result = $this->companyService->list(
            userId: $user->id, 
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() > 1);
    }

    public function test_company_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companies = Company::factory()->count(25)->create([]);
        for ($i = 0; $i < $companies->count(); $i++) {
            $user->companies()->attach([$companies[$i]->id]);
        }

        $result = $this->companyService->list(
            userId: $user->id, 
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
    public function test_company_service_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();
        
        $company = $user->companies->first()->inRandomOrder()->first();

        $result = $this->companyService->read($company);

        $this->assertInstanceOf(Company::class, $result);
    }
    /* #endregion */

    /* #region update */
    public function test_company_service_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyArr = Company::factory()->make();

        $result = $this->companyService->update($company, $companyArr->toArray());
        
        $this->assertInstanceOf(Company::class, $result);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
        ]);
    }

    public function test_company_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyArr = [];
            
        $this->companyService->update($company, $companyArr);
    }
    /* #endregion */

    /* #region delete */
    public function test_company_service_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
            
        $result = $this->companyService->delete($company);
        
        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('companies', [
            'id' => $company->id
        ]);
    }
    /* #endregion */

    /* #region others */
    public function test_company_service_call_function_get_all_active_company_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->companyService->getAllActiveCompany(
            userId: $user->id
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_company_service_call_function_get_default_company_expect_default_company_returned()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->companyService->getDefaultCompany(
            $user,
        );

        $this->assertTrue(boolval($result));
    }
    
    public function test_company_service_call_function_get_is_default_company_expect_default_company_returned()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $result = $this->companyService->isDefaultCompany(
            company: $company,
        );
        $this->assertTrue(boolval($result));
    }

    public function test_company_service_call_function_reset_default_company_expect_no_default_company_exists()
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5 ; $i++) {
            if ($i == 0) {
                $company = Company::factory()->setIsDefault()->create([]);
                $companyId = $company->id;
            } else {
                $company = Company::factory()->create([]);
                $companyId = $company->id;
            }

            $user->companies()->attach([$companyId]);
        }

        $result = $this->companyService->resetDefaultCompany(
            user: $user
        );

        $this->assertTrue($result);

        $defaultCount = $user->companies->where('default', '=', 'true')->count();
        $this->assertTrue($defaultCount == 0);
    }

    public function test_company_service_call_function_generateUniqueCode_expect_unique_code_returned()
    {
        $this->assertIsString($this->companyService->generateUniqueCode());
    }

    public function test_company_service_call_function_isUniqueCode_expect_can_detect_unique_code()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $company1 = Company::factory()->create([
            'code' => 'test1'
        ]);
        $user->companies()->attach([$company1->id]);

        $company2 = Company::factory()->create([
            'code' => 'test2'
        ]);
        $user->companies()->attach([$company2->id]);

        $this->assertFalse($this->companyService->isUniqueCode('test1', $userId));
        $this->assertFalse($this->companyService->isUniqueCode('test2', $userId));
        $this->assertTrue($this->companyService->isUniqueCode('test3', $userId));
        $this->assertTrue($this->companyService->isUniqueCode('test4', $userId));
    }
    /* #endregion */
}
