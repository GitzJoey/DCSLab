<?php

namespace Tests\Unit\Actions\CustomerActions;

use App\Actions\Customer\CustomerActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class CustomerActionsReadTest extends ActionsTestCase
{
    private CustomerActions $customerActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerActions = new CustomerActions();
    }

    public function test_customer_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Customer::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->customerActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            search: '',
            isMember: null,
            groupId: null,
            zone: null,
            maxOpenInvoice: null,
            maxOutstandingInvoice: null,
            maxInvoiceAge: null,
            paymentTermType: null,
            paymentTerm: null,
            taxableEnterprise: null,
            taxId: null,
            status: null,
            includeId: null,
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

    public function test_customer_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Customer::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->customerActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            search: '',
            isMember: null,
            groupId: null,
            zone: null,
            maxOpenInvoice: null,
            maxOutstandingInvoice: null,
            maxInvoiceAge: null,
            paymentTermType: null,
            paymentTerm: null,
            taxableEnterprise: null,
            taxId: null,
            status: null,
            includeId: null,
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

    public function test_customer_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->customerActions->readAny(
            withTrashed: false,
            companyId: $maxId,
            search: '',
            isMember: null,
            groupId: null,
            zone: null,
            maxOpenInvoice: null,
            maxOutstandingInvoice: null,
            maxInvoiceAge: null,
            paymentTermType: null,
            paymentTerm: null,
            taxableEnterprise: null,
            taxId: null,
            status: null,
            includeId: null,
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

    public function test_customer_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $customerCount = 4;
        $idxTest = random_int(0, $customerCount - 1);
        $defaultName = Customer::factory()->make()->name;
        $testname = Customer::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Customer::factory()->count($customerCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testname : $defaultName,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->customerActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            search: 'testing',
            isMember: null,
            groupId: null,
            zone: null,
            maxOpenInvoice: null,
            maxOutstandingInvoice: null,
            maxInvoiceAge: null,
            paymentTermType: null,
            paymentTerm: null,
            taxableEnterprise: null,
            taxId: null,
            status: null,
            includeId: null,
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

    public function test_customer_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_customer_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_customer_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Customer::factory())
            )->create();

        $customer = $user->companies()->inRandomOrder()->first()
            ->customers()->inRandomOrder()->first();

        $result = $this->customerActions->read($customer);

        $this->assertInstanceOf(Customer::class, $result);
    }
}
