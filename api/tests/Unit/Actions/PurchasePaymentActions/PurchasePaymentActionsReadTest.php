<?php

namespace Tests\Unit\Actions\PurchasePaymentActions;

use App\Actions\PurchasePayment\PurchasePaymentActions;
use App\Models\Company;
use App\Models\PurchasePayment;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class PurchasePaymentActionsReadTest extends ActionsTestCase
{
    private PurchasePaymentActions $purchasePaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchasePaymentActions = new PurchasePaymentActions();
    }

    public function test_purchase_payment_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchasePayment::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->purchasePaymentActions->readAny(
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

    public function test_purchase_payment_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchasePayment::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->purchasePaymentActions->readAny(
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

    public function test_purchase_payment_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->purchasePaymentActions->readAny(
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

    public function test_purchase_payment_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $purchasePaymentCount = 4;
        $idxTest = random_int(0, $purchasePaymentCount - 1);
        $defaultName = PurchasePayment::factory()->make()->name;
        $testname = PurchasePayment::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchasePayment::factory()->count($purchasePaymentCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testname : $defaultName,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->purchasePaymentActions->readAny(
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

    public function test_purchase_payment_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_purchase_payment_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_purchase_payment_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchasePayment::factory())
            )->create();

        $purchasePayment = $user->companies()->inRandomOrder()->first()
            ->purchasePayments()->inRandomOrder()->first();

        $result = $this->purchasePaymentActions->read($purchasePayment);

        $this->assertInstanceOf(PurchasePayment::class, $result);
    }
}
