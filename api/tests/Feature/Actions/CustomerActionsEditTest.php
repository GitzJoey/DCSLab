<?php

namespace Tests\Feature;

use App\Actions\Customer\CustomerActions;
use App\Actions\RandomGenerator;
use App\Enums\RecordStatus;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\CustomerGroupTableSeeder;
use Database\Seeders\CustomerTableSeeder;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerActionsEditTest extends TestCase
{
    use WithFaker;

    private $customerActions;

    private $customerAddressActions;

    private $companySeeder;

    private $customerGroupSeeder;

    private $customerSeeder;

    private $randomGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerActions = app(CustomerActions::class);
        $this->companySeeder = new CompanyTableSeeder();
        $this->customerGroupSeeder = new CustomerGroupTableSeeder();
        $this->customerSeeder = new CustomerTableSeeder();

        $this->randomGenerator = new RandomGenerator();
    }

    public function test_customer_actions_call_update_customer_and_insert_customer_address_expect_db_updated()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [5, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->customerGroupSeeder->callWith(CustomerGroupTableSeeder::class, [5, $companyId]);
        $this->customerSeeder->callWith(CustomerTableSeeder::class, [10, $companyId]);

        $customer = $company->customers()->inRandomOrder()->first();

        $customerArr = Customer::factory()->setStatusActive()->make([
            'company_id' => $companyId,
            'customer_group_id' => CustomerGroup::where('company_id', '=', $companyId)->inRandomOrder()->first()->id,
        ])->toArray();

        $customerAddressesArr = $customer->customerAddresses->toArray();
        $newCustomerAddresses = [
            'id' => null,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'contact' => $this->faker->e164PhoneNumber(),
            'is_main' => false,
            'remarks' => $this->faker->sentence(),
        ];

        array_push($customerAddressesArr, $newCustomerAddresses);

        $picArr = Profile::factory()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).$this->randomGenerator->generateNumber(1, 999);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = $user['password'];
        $picArr['status'] = $customerArr['status'];

        $result = $this->customerActions->update(
            $customer,
            $customerArr,
            $customerAddressesArr,
            $picArr
        );

        $this->assertInstanceOf(Customer::class, $result);

        $this->assertDatabaseHas('customers', [
            // 'ulid' => $result->id,
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
            'company_id' => $companyId,
            'customer_id' => $result->id,
            'address' => $customerAddressesArr[0]['address'],
            'city' => $customerAddressesArr[0]['city'],
            'contact' => $customerAddressesArr[0]['contact'],
            'is_main' => $customerAddressesArr[0]['is_main'],
            'remarks' => $customerAddressesArr[0]['remarks'],
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => $picArr['first_name'],
            'last_name' => $picArr['last_name'],
            'status' => RecordStatus::ACTIVE->value,
        ]);
    }

    public function test_customer_actions_call_update_customer_and_edit_customer_address_expect_db_updated()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->customerGroupSeeder->callWith(CustomerGroupTableSeeder::class, [5, $companyId]);
        $this->customerSeeder->callWith(CustomerTableSeeder::class, [10, $companyId]);

        $customer = $company->customers()->inRandomOrder()->first();

        $customerArr = Customer::factory()->setStatusActive()->make([
            'company_id' => $companyId,
            'customer_group_id' => CustomerGroup::where('company_id', '=', $companyId)->inRandomOrder()->first()->id,
        ])->toArray();

        $customerAddressesArr = $customer->customerAddresses->toArray();

        $lastRow = count($customerAddressesArr) - 1;
        $customerAddressesArr[$lastRow]['id'] = null;
        $customerAddressesArr[$lastRow]['address'] = $this->faker->address();
        $customerAddressesArr[$lastRow]['city'] = $this->faker->city();
        $customerAddressesArr[$lastRow]['contact'] = $this->faker->e164PhoneNumber();
        $customerAddressesArr[$lastRow]['is_base'] = false;
        $customerAddressesArr[$lastRow]['remarks'] = $this->faker->sentence();

        $picArr = Profile::factory()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).$this->randomGenerator->generateNumber(1, 999);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = $user['password'];
        $picArr['status'] = $customerArr['status'];

        $result = $this->customerActions->update(
            $customer,
            $customerArr,
            $customerAddressesArr,
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
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'address' => $customerAddressesArr[0]['address'],
            'city' => $customerAddressesArr[0]['city'],
            'contact' => $customerAddressesArr[0]['contact'],
            'is_main' => $customerAddressesArr[0]['is_main'],
            'remarks' => $customerAddressesArr[0]['remarks'],
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => $picArr['first_name'],
            'last_name' => $picArr['last_name'],
            'status' => RecordStatus::ACTIVE->value,
        ]);
    }

    public function test_customer_actions_call_update_customer_and_delete_customer_address_expect_db_updated()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->customerGroupSeeder->callWith(CustomerGroupTableSeeder::class, [5, $companyId]);
        $this->customerSeeder->callWith(CustomerTableSeeder::class, [10, $companyId]);

        do {
            $customer = $company->customers()->inRandomOrder()->first();
        } while ($customer->customerAddresses()->count() == 1);

        $customerArr = Customer::factory()->setStatusActive()->make([
            'company_id' => $companyId,
            'customer_group_id' => CustomerGroup::where('company_id', '=', $companyId)->inRandomOrder()->first()->id,
        ])->toArray();

        $customerAddressesArr = $customer->customerAddresses->toArray();

        array_pop($customerAddressesArr);

        $picArr = Profile::factory()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).$this->randomGenerator->generateNumber(1, 999);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = $user['password'];
        $picArr['status'] = $customerArr['status'];

        $result = $this->customerActions->update(
            $customer,
            $customerArr,
            $customerAddressesArr,
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
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'address' => $customerAddressesArr[0]['address'],
            'city' => $customerAddressesArr[0]['city'],
            'contact' => $customerAddressesArr[0]['contact'],
            'is_main' => $customerAddressesArr[0]['is_main'],
            'remarks' => $customerAddressesArr[0]['remarks'],
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
        $this->customerActions->create(
            [],
            [],
            []
        );

        $this->customerActions->update(
            [],
            [],
            []
        );
    }
}
