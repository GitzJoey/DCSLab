<?php

namespace Tests\Unit\Actions\PurchaseProductUnitActions;

use App\Actions\PurchaseProductUnit\PurchaseProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseProductUnit;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class PurchaseProductUnitActionsReadTest extends ActionsTestCase
{
    private PurchaseProductUnitActions $purchaseProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseProductUnitActions = new PurchaseProductUnitActions();
    }

    public function test_purchase_product_unit_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->purchaseProductUnitActions->readAny(
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

    public function test_purchase_product_unit_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->purchaseProductUnitActions->readAny(
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

    public function test_purchase_product_unit_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->purchaseProductUnitActions->readAny(
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

    public function test_purchase_product_unit_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $purchaseProductUnitCount = 4;
        $idxTest = random_int(0, $purchaseProductUnitCount - 1);
        $defaultName = PurchaseProductUnit::factory()->make()->name;
        $testname = PurchaseProductUnit::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnit::factory()->count($purchaseProductUnitCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testname : $defaultName,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->purchaseProductUnitActions->readAny(
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

    public function test_purchase_product_unit_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_purchase_product_unit_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_purchase_product_unit_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnit::factory())
            )->create();

        $purchaseProductUnit = $user->companies()->inRandomOrder()->first()
            ->purchaseProductUnits()->inRandomOrder()->first();

        $result = $this->purchaseProductUnitActions->read($purchaseProductUnit);

        $this->assertInstanceOf(PurchaseProductUnit::class, $result);
    }
}
