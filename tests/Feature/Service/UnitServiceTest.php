<?php

namespace Tests\Feature\Service;

use Exception;
use App\Models\Unit;
use App\Models\User;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Enums\UnitCategory;
use App\Services\UnitService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class UnitServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitService = app(UnitService::class);
    }

    #region create
    public function test_unit_service_call_create_expect_db_has_record()
    {
        $anu = Unit::first()->toArray();
        
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();
        
        $unitArr = Unit::factory()->make([
            'company_id' => $user->companies->first()->id
        ]);

        $result = $this->unitService->create($unitArr->toArray());

        $this->assertDatabaseHas('units', [
            'id' => $result->id,
            'company_id' => $unitArr['company_id'],
            'code' => $unitArr['code'],
            'name' => $unitArr['name'],
            'description' => $unitArr['description'],
            'category' => $unitArr['category'],
        ]);
    }

    public function test_unit_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->unitService->create([]);
    }

    #endregion

    #region list

    public function test_unit_service_call_list_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(20), 'units'), 'companies')
                    ->create();

        $unitCategory = $this->faker->randomElement(UnitCategory::toArrayValue());

        $result = $this->unitService->list(
            companyId: $user->companies->first()->id,
            unitCategory: $unitCategory,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_unit_service_call_list_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(20), 'units'), 'companies')
                    ->create();

        $unitCategory = $this->faker->randomElement(UnitCategory::toArrayValue());

        $result = $this->unitService->list(
            companyId: $user->companies->first()->id,
            unitCategory: $unitCategory,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_unit_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $unitCategory = UnitCategory::PRODUCTS_AND_SERVICES->value;

        $result = $this->unitService->list(
            companyId: $maxId,
            unitCategory: $unitCategory,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_unit_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        Unit::factory()->insertStringInName(' testing')->count(10)->create([
            'company_id' => $companyId
        ]);

        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
        ]);

        $unitCategory = UnitCategory::PRODUCTS_AND_SERVICES->value;

        $result = $this->unitService->list(
            companyId: $companyId,
            unitCategory: $unitCategory,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_unit_service_call_list_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        Unit::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);

        $unitCategory = UnitCategory::PRODUCTS_AND_SERVICES->value;
        
        $result = $this->unitService->list(
            companyId: $companyId, 
            unitCategory: $unitCategory,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() > 1);
    }

    public function test_unit_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        Unit::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);
        
        $unitCategory = UnitCategory::PRODUCTS_AND_SERVICES->value;

        $result = $this->unitService->list(
            companyId: $companyId, 
            unitCategory: $unitCategory,
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

    public function test_unit_service_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Unit::factory()->count(20), 'units'), 'companies')
                    ->create();
        
        $unit = $user->companies->first()->units()->inRandomOrder()->first();

        $result = $this->unitService->read($unit);

        $this->assertInstanceOf(Unit::class, $result);
    }

    #endregion

    #region update

    public function test_unit_service_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Unit::factory(), 'units'), 'companies')
                    ->create();

        $unit = $user->companies->first()->units->first();
        $unitArr = Unit::factory()->make();

        $result = $this->unitService->update($unit, $unitArr->toArray());
        
        $this->assertInstanceOf(Unit::class, $result);
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'company_id' => $unit->company_id,
            'code' => $unitArr['code'],
            'name' => $unitArr['name'],
        ]);
    }

    public function test_unit_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Unit::factory(), 'units'), 'companies')
                    ->create();

        $units = $user->companies->first()->units->first();
        $unitsArr = [];
            
        $this->unitsService->update($units, $unitsArr);
    }

    #endregion

    #region delete

    public function test_unit_service_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Unit::factory()->count(5), 'units'), 'companies')
                    ->create();

        $units = $user->companies->first()->units->first();
            
        $result = $this->unitService->delete($units);
        
        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('units', [
            'id' => $units->id
        ]);
    }

    #endregion

    #region others

    #endregion
}