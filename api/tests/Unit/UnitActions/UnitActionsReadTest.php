<?php

namespace Tests\Unit;

use App\Actions\Unit\UnitActions;
use App\Enums\UnitCategory;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ActionsTestCase;

class UnitActionsReadTest extends ActionsTestCase
{
    use WithFaker;

    private UnitActions $unitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitActions = new UnitActions();
    }

    public function test_unit_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Unit::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->unitActions->readAny(
            companyId: $company->id,
            category: $this->faker->randomElement(UnitCategory::toArrayValue()),
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_unit_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Unit::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->unitActions->readAny(
            companyId: $company->id,
            category: $this->faker->randomElement(UnitCategory::toArrayValue()),
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_unit_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->unitActions->readAny(
            companyId: $maxId,
            category: $this->faker->randomElement(UnitCategory::toArrayValue()),
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_unit_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Unit::factory()->count(5))
                    ->has(Unit::factory()->insertStringInName('testing')->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->unitActions->readAny(
            companyId: $company->id,
            category: -1,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_unit_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Unit::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->unitActions->readAny(
            companyId: $company->id,
            category: null,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_unit_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Unit::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->unitActions->readAny(
            companyId: $company->id,
            category: null,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_unit_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Unit::factory()->count(5))
            )->create();

        $unit = $user->companies()->inRandomOrder()->first()
            ->units()->inRandomOrder()->first();

        $result = $this->unitActions->read($unit);

        $this->assertInstanceOf(Unit::class, $result);
    }
}
