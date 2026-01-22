<?php

namespace Tests\Unit\Actions\SaleProductUnitActions;

use App\Actions\SaleProductUnit\SaleProductUnitActions;
use App\Models\Company;
use App\Models\SaleProductUnit;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class SaleProductUnitActionsReadTest extends ActionsTestCase
{
    private SaleProductUnitActions $saleProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleProductUnitActions = new SaleProductUnitActions();
    }

    public function test_sale_product_unit_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->saleProductUnitActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: true,
            page: 1,
            perPage: 10,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_sale_product_unit_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->saleProductUnitActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: false,
            page: null,
            perPage: null,
            limit: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_sale_product_unit_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->saleProductUnitActions->readAny(
            companyId: $maxId,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: false,
            page: null,
            perPage: null,
            limit: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_sale_product_unit_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $saleProductUnitCount = 4;
        $idxTest = random_int(0, $saleProductUnitCount - 1);
        $defaultName = SaleProductUnit::factory()->make()->name;
        $testname = SaleProductUnit::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnit::factory()->count($saleProductUnitCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testname : $defaultName,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->saleProductUnitActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: 'testing',

            paginate: true,
            page: 1,
            perPage: 10,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 1);
    }

    public function test_sale_product_unit_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_sale_product_unit_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_sale_product_unit_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnit::factory())
            )->create();

        $saleProductUnit = $user->companies()->inRandomOrder()->first()
            ->saleProductUnits()->inRandomOrder()->first();

        $result = $this->saleProductUnitActions->read($saleProductUnit);

        $this->assertInstanceOf(SaleProductUnit::class, $result);
    }
}
