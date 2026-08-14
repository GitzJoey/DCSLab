<?php

namespace Tests\Unit\Actions\SupplierActions;

use App\Actions\Supplier\SupplierActions;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class SupplierActionsReadTest extends ActionsTestCase
{
    private SupplierActions $supplierActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplierActions = new SupplierActions();
    }

    public function test_supplier_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        Supplier::factory()->for($company)->for($user)
            ->create();

        $result = $this->supplierActions->readAny(
            user: $user,
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

    public function test_supplier_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Supplier::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->supplierActions->readAny(
            user: $user,
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

    public function test_supplier_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->supplierActions->readAny(
            user: User::factory()->create(),
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

    public function test_supplier_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $supplierCount = 4;
        $idxTest = random_int(0, $supplierCount - 1);
        $defaultName = Supplier::factory()->make()->name;
        $testname = Supplier::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Supplier::factory()->count($supplierCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testname : $defaultName,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->supplierActions->readAny(
            user: $user,
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

    public function test_supplier_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_supplier_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_supplier_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Supplier::factory())
            )->create();

        $supplier = $user->companies()->inRandomOrder()->first()
            ->suppliers()->inRandomOrder()->first();

        $result = $this->supplierActions->read($supplier);

        $this->assertInstanceOf(Supplier::class, $result);
    }
}
