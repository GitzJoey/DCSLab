<?php

namespace Tests\Unit;

use App\Actions\Customer\CustomerActions;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ActionsTestCase;

class CustomerActionsReadTest extends ActionsTestCase
{
    use WithFaker;

    private CustomerActions $customerActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerActions = new CustomerActions();
    }

    public function test_customer_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()->for($company)->for($customerGroup)->create();

        $result = $this->customerActions->read($customer);

        $this->assertInstanceOf(Customer::class, $result);
    }

    public function test_customer_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(
                CustomerAddress::factory()
                    ->for($company)
                    ->count($this->faker->numberBetween(1, 5))
            )->create();

        $result = $this->customerActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_customer_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(
                CustomerAddress::factory()
                    ->for($company)
                    ->count($this->faker->numberBetween(1, 5))
            )->create();

        $result = $this->customerActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: false,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_customer_actions_call_read_any_with_nonexistance_company_id_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;
        $result = $this->customerActions->readAny(
            companyId: $maxId,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_customer_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(
                CustomerAddress::factory()
                    ->for($company)
                    ->count($this->faker->numberBetween(1, 5))
            )->count(2)->create();

        Customer::factory()
            ->for($company)->for($customerGroup)
            ->insertStringInName('testing')
            ->has(
                CustomerAddress::factory()
                    ->for($company)
                    ->count($this->faker->numberBetween(1, 5))
            )->count(3)->create();

        $result = $this->customerActions->readAny(
            companyId: $company->id,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_customer_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(
                CustomerAddress::factory()
                    ->for($company)
                    ->count($this->faker->numberBetween(1, 5))
            )->count(3)->create();

        $result = $this->customerActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_customer_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(
                CustomerAddress::factory()
                    ->for($company)
                    ->count($this->faker->numberBetween(1, 5))
            )->count(3)->create();

        $result = $this->customerActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() > 1);
    }
}
