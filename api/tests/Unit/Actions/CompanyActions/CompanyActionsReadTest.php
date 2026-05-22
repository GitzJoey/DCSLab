<?php

namespace Tests\Unit\Actions\CompanyActions;

use App\Actions\Company\CompanyActions;
use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class CompanyActionsReadTest extends ActionsTestCase
{
    private CompanyActions $companyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = new CompanyActions();
    }

    public function test_company_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_company_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_company_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $companyCount = random_int(1, 4);
        $idxDefaultCompany = random_int(0, $companyCount - 1);
        $idxTest = random_int(0, $companyCount - 1);
        $defaultName = Company::factory()->make()->name;
        $testName = Company::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->count($companyCount)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'default' => $sequence->index == $idxDefaultCompany ? true : false,
                        'name' => $sequence->index == $idxTest ? $testName : $defaultName,
                    ]
                ))
            )
            ->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 1);
    }

    public function test_company_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $companyCount = 5;
        $idxDefaultCompany = random_int(0, $companyCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->count($companyCount)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'default' => $sequence->index == $idxDefaultCompany ? true : false,
                    ]
                ))
            )
            ->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_company_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $companyCount = 5;
        $idxDefaultCompany = random_int(0, $companyCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->count($companyCount)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'default' => $sequence->index == $idxDefaultCompany ? true : false,
                    ]
                ))
            )
            ->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_company_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->companyActions->read($company);

        $this->assertInstanceOf(Company::class, $result);
    }
}
