<?php

namespace Tests\Unit;

use App\Actions\Customer\CustomerActions;
use App\Enums\RecordStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use App\Models\Profile;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CustomerActionsEditTest extends ActionsTestCase
{
    private CustomerActions $customerActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerActions = new CustomerActions();
    }

    public function test_customer_actions_call_update_customer_and_insert_customer_address_expect_db_updated()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()->for($company)->for($customerGroup)
            ->has(CustomerAddress::factory()->for($company)->count(3))
            ->create();

        $customerArr = Customer::factory()->for($company)->for($customerGroup)
            ->setStatusActive()->make()->toArray();

        $customerAddressArr = $customer->customerAddresses()->get()->toArray();
        array_push(
            $customerAddressArr,
            CustomerAddress::factory()->make(['id' => ''])->toArray()
        );

        $picArr = Profile::factory()->setStatusActive()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).random_int(1, 5);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = '123456';
        $picArr['contact'] = $customerAddressArr[0]['contact'];
        $picArr['address'] = $customerAddressArr[0]['address'];
        $picArr['city'] = $customerAddressArr[0]['city'];
        $picArr['tax_id'] = $customerArr['tax_id'];

        $result = $this->customerActions->update(
            $customer,
            $customerArr,
            $customerAddressArr,
            $picArr
        );

        $this->assertInstanceOf(Customer::class, $result);

        $this->assertDatabaseHas('customers', [
            'id' => $result->id,
            'company_id' => $customerArr['company_id'],
            'customer_group_id' => $customerArr['customer_group_id'],
            'code' => $customerArr['code'],
            'is_member' => $customerArr['is_member'],
            'name' => $customerArr['name'],
            'zone' => $customerArr['zone'],
            'max_open_invoice' => $customerArr['max_open_invoice'],
            'max_outstanding_invoice' => $customerArr['max_outstanding_invoice'],
            'max_invoice_age' => $customerArr['max_invoice_age'],
            'payment_term_type' => $customerArr['payment_term_type'],
            'payment_term' => $customerArr['payment_term'],
            'taxable_enterprise' => $customerArr['taxable_enterprise'],
            'tax_id' => $customerArr['tax_id'],
            'remarks' => $customerArr['remarks'],
            'status' => $customerArr['status'],
        ]);

        foreach ($customerAddressArr as $customerAddress) {
            $this->assertDatabaseHas('customer_addresses', [
                'company_id' => $company->id,
                'customer_id' => $result->id,
                'address' => $customerAddress['address'],
                'city' => $customerAddress['city'],
                'contact' => $customerAddress['contact'],
                'is_main' => $customerAddress['is_main'],
                'remarks' => $customerAddress['remarks'],
            ]);
        }

        $this->assertDatabaseHas('profiles', [
            'first_name' => $picArr['first_name'],
            'last_name' => $picArr['last_name'],
            'status' => RecordStatus::ACTIVE->value,
        ]);
    }

    public function test_customer_actions_call_update_customer_and_edit_customer_address_expect_db_updated()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(CustomerAddress::factory()->for($company)->count(3))
            ->create();

        $customerArr = Customer::factory()
            ->for($company)->for($customerGroup)
            ->setStatusActive()->make()->toArray();

        $customerAddressArr = $customer->customerAddresses->toArray();

        $lastRow = count($customerAddressArr) - 1;
        $customerAddressFactory = CustomerAddress::factory()->make();
        $customerAddressArr[$lastRow]['id'] = null;
        $customerAddressArr[$lastRow]['address'] = $customerAddressFactory->address;
        $customerAddressArr[$lastRow]['city'] = $customerAddressFactory->city;
        $customerAddressArr[$lastRow]['contact'] = $customerAddressFactory->contact;
        $customerAddressArr[$lastRow]['is_main'] = $customerAddressFactory->is_main;
        $customerAddressArr[$lastRow]['remarks'] = $customerAddressFactory->sentence;

        $picArr = Profile::factory()->setStatusActive()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).random_int(1, 5);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = '123456';
        $picArr['contact'] = $customerAddressArr[0]['contact'];
        $picArr['address'] = $customerAddressArr[0]['address'];
        $picArr['city'] = $customerAddressArr[0]['city'];
        $picArr['tax_id'] = $customerArr['tax_id'];

        $result = $this->customerActions->update(
            $customer,
            $customerArr,
            $customerAddressArr,
            $picArr
        );

        $this->assertInstanceOf(Customer::class, $result);

        $this->assertDatabaseHas('customers', [
            'ulid' => $customer->ulid,
            'company_id' => $customerArr['company_id'],
            'customer_group_id' => $customerArr['customer_group_id'],
            'code' => $customerArr['code'],
            'is_member' => $customerArr['is_member'],
            'name' => $customerArr['name'],
            'zone' => $customerArr['zone'],
            'max_open_invoice' => $customerArr['max_open_invoice'],
            'max_outstanding_invoice' => $customerArr['max_outstanding_invoice'],
            'max_invoice_age' => $customerArr['max_invoice_age'],
            'payment_term_type' => $customerArr['payment_term_type'],
            'payment_term' => $customerArr['payment_term'],
            'taxable_enterprise' => $customerArr['taxable_enterprise'],
            'tax_id' => $customerArr['tax_id'],
            'remarks' => $customerArr['remarks'],
            'status' => $customerArr['status'],
        ]);

        $this->assertDatabaseHas('customer_addresses', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'address' => $customerAddressArr[0]['address'],
            'city' => $customerAddressArr[0]['city'],
            'contact' => $customerAddressArr[0]['contact'],
            'is_main' => $customerAddressArr[0]['is_main'],
            'remarks' => $customerAddressArr[0]['remarks'],
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => $picArr['first_name'],
            'last_name' => $picArr['last_name'],
            'status' => RecordStatus::ACTIVE->value,
        ]);
    }

    public function test_customer_actions_call_update_customer_and_delete_customer_address_expect_db_updated()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(CustomerAddress::factory()->for($company)->count(3))
            ->create();

        $customerArr = Customer::factory()
            ->for($company)->for($customerGroup)
            ->make()->toArray();

        $customerAddressArr = $customer->customerAddresses->toArray();

        array_pop($customerAddressArr);

        $picArr = Profile::factory()->setStatusActive()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).random_int(1, 5);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = '123456';
        $picArr['contact'] = $customerAddressArr[0]['contact'];
        $picArr['address'] = $customerAddressArr[0]['address'];
        $picArr['city'] = $customerAddressArr[0]['city'];
        $picArr['tax_id'] = $customerArr['tax_id'];

        $result = $this->customerActions->update(
            $customer,
            $customerArr,
            $customerAddressArr,
            $picArr
        );

        $this->assertInstanceOf(Customer::class, $result);

        $this->assertDatabaseHas('customers', [
            'ulid' => $customer->ulid,
            'company_id' => $customerArr['company_id'],
            'customer_group_id' => $customerArr['customer_group_id'],
            'code' => $customerArr['code'],
            'is_member' => $customerArr['is_member'],
            'name' => $customerArr['name'],
            'zone' => $customerArr['zone'],
            'max_open_invoice' => $customerArr['max_open_invoice'],
            'max_outstanding_invoice' => $customerArr['max_outstanding_invoice'],
            'max_invoice_age' => $customerArr['max_invoice_age'],
            'payment_term_type' => $customerArr['payment_term_type'],
            'payment_term' => $customerArr['payment_term'],
            'taxable_enterprise' => $customerArr['taxable_enterprise'],
            'tax_id' => $customerArr['tax_id'],
            'remarks' => $customerArr['remarks'],
            'status' => $customerArr['status'],
        ]);

        $this->assertDatabaseHas('customer_addresses', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'address' => $customerAddressArr[0]['address'],
            'city' => $customerAddressArr[0]['city'],
            'contact' => $customerAddressArr[0]['contact'],
            'is_main' => $customerAddressArr[0]['is_main'],
            'remarks' => $customerAddressArr[0]['remarks'],
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => $picArr['first_name'],
            'last_name' => $picArr['last_name'],
            'status' => RecordStatus::ACTIVE->value,
        ]);
    }

    public function test_customer_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(CustomerAddress::factory()->for($company)->count(3))
            ->create();

        $customerArr = [];
        $customerAddressArr = [];
        $picArr = [];

        $this->customerActions->update(
            $customer,
            $customerArr,
            $customerAddressArr,
            $picArr
        );
    }
}
