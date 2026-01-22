<?php

namespace Tests\Unit\Actions\PurchaseReceiptActions;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use App\Models\Company;
use App\Models\PurchaseReceipt;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class PurchaseReceiptActionsReadTest extends ActionsTestCase
{
    private PurchaseReceiptActions $purchaseReceiptActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptActions = new PurchaseReceiptActions();
    }

    public function test_purchase_receipt_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceipt::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->purchaseReceiptActions->readAny(
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

    public function test_purchase_receipt_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceipt::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->purchaseReceiptActions->readAny(
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

    public function test_purchase_receipt_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->purchaseReceiptActions->readAny(
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

    public function test_purchase_receipt_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $purchaseReceiptCount = 4;
        $idxTest = random_int(0, $purchaseReceiptCount - 1);
        $defaultName = PurchaseReceipt::factory()->make()->name;
        $testname = PurchaseReceipt::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceipt::factory()->count($purchaseReceiptCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testname : $defaultName,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->purchaseReceiptActions->readAny(
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

    public function test_purchase_receipt_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_purchase_receipt_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_purchase_receipt_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceipt::factory())
            )->create();

        $purchaseReceipt = $user->companies()->inRandomOrder()->first()
            ->purchaseReceipts()->inRandomOrder()->first();

        $result = $this->purchaseReceiptActions->read($purchaseReceipt);

        $this->assertInstanceOf(PurchaseReceipt::class, $result);
    }
}
