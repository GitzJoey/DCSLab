<?php

namespace Tests\Feature;

use App\Actions\Customer\CustomerActions;
use App\Enums\RecordStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use App\Models\Profile;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ActionsTestCase;

class CustomerActionsCreateTest extends ActionsTestCase
{
    use WithFaker;

    private CustomerActions $customerActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerActions = new CustomerActions();
    }

    public function test_customer_actions_call_create_customer_expect_db_has_record()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(5))
                )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customerArr = Customer::factory()->for($company)->for($customerGroup)->make()->toArray();

        $customerAddressArr = CustomerAddress::factory()->for($company)
                                ->count($this->faker->numberBetween(1, 5))
                                ->make()->toArray();

        $picArr = Profile::factory()->setStatusActive()->for($user)->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).$this->faker->numberBetween(1, 999);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = '123456';
        $picArr['contact'] = $customerAddressArr[0]['contact'];
        $picArr['address'] = $customerAddressArr[0]['address'];
        $picArr['city'] = $customerAddressArr[0]['city'];
        $picArr['tax_id'] = $customerArr['tax_id'];

        $result = $this->customerActions->create(
            $customerArr,
            $customerAddressArr,
            $picArr,
        );

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

        $this->assertDatabaseHas('customer_addresses', [
            'company_id' => $company->id,
            'customer_id' => $result->id,
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

    public function test_customer_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->customerActions->create(
            [],
            [],
            []
        );
    }
}
