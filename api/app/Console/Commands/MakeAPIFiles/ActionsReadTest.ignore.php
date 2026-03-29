<?php

namespace Tests\Unit\Actions\RepToPascalThisActions;

use App\Actions\RepToPascalThis\RepToPascalThisActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Models\Company;
use App\Models\RepToPascalThis;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class RepToPascalThisActionsReadTest extends ActionsTestCase
{
    private RepToPascalThisActions $RepToCamelThisActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->RepToCamelThisActions = new RepToPascalThisActions();
    }

    public function test_RepToSnakeThis_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(RepToPascalThis::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->RepToCamelThisActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            search: '',
            execute: new ExecuteDTO(
                useCache: true,
                pagination: new ExecutePaginationDTO(
                    page: 1,
                    perPage: 10,
                ),
                get: null,
            )
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_RepToSnakeThis_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(RepToPascalThis::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->RepToCamelThisActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            search: '',
            execute: new ExecuteDTO(
                useCache: true,
                pagination: null,
                get: new ExecuteGetDTO(
                    limit: 10,
                ),
            )
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_RepToSnakeThis_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->RepToCamelThisActions->readAny(
            withTrashed: false,
            companyId: $maxId,
            search: '',
            execute: new ExecuteDTO(
                useCache: true,
                pagination: null,
                get: new ExecuteGetDTO(
                    limit: 10,
                ),
            )
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_RepToSnakeThis_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $RepToCamelThisCount = 4;
        $idxTest = random_int(0, $RepToCamelThisCount - 1);
        $defaultCode = RepToPascalThis::factory()->make()->code;
        $testCode = 'testing-'.$defaultCode;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(RepToPascalThis::factory()->count($RepToCamelThisCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'code' => $sequence->index == $idxTest ? $testCode : $defaultCode,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->RepToCamelThisActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            search: 'testing',
            execute: new ExecuteDTO(
                useCache: true,
                pagination: new ExecutePaginationDTO(
                    page: 1,
                    perPage: 10,
                ),
                get: null,
            )
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 1);
    }

    public function test_RepToSnakeThis_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_RepToSnakeThis_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_RepToSnakeThis_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(RepToPascalThis::factory())
            )->create();

        $RepToCamelThis = $user->companies()->inRandomOrder()->first()
            ->RepToCamelPluralsThis()->inRandomOrder()->first();

        $result = $this->RepToCamelThisActions->read($RepToCamelThis);

        $this->assertInstanceOf(RepToPascalThis::class, $result);
    }
}
