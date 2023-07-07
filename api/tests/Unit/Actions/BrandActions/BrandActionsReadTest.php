<?php

namespace Tests\Unit\Actions\BrandActions;

use App\Actions\Brand\BrandActions;
use App\Models\Brand;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class BrandActionsReadTest extends ActionsTestCase
{
    private BrandActions $brandActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandActions = new BrandActions();
    }

    public function test_brand_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->brandActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: false,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_brand_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;
        $result = $this->brandActions->readAny(
            companyId: $maxId,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_brand_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $brandCount = 5;
        $idxTest = random_int(0, $brandCount - 1);
        $defaultName = Brand::factory()->make()->name;
        $testName = Brand::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory()->count($brandCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testName : $defaultName,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->brandActions->readAny(
            companyId: $company->id,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 1);
    }

    public function test_brand_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->brandActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_brand_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->brandActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_brand_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory()->count(5))
            )->create();

        $brand = $user->companies()->inRandomOrder()->first()
            ->brands()->inRandomOrder()->first();

        $result = $this->brandActions->read($brand);

        $this->assertInstanceOf(Brand::class, $result);
    }

    public function test_brand_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->brandActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }
}
