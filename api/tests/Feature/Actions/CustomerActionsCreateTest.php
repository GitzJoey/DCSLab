<?php

namespace Tests\Feature;

use App\Actions\Customer\CustomerActions;
use App\Actions\RandomGenerator;
use App\Enums\RecordStatus;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\CustomerGroupTableSeeder;
use Database\Seeders\CustomerTableSeeder;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerActionsCreateTest extends TestCase
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

    public function test_customer_actions_call_create_customer_expect_db_has_record()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->customerGroupSeeder->callWith(CustomerGroupTableSeeder::class, [3, $companyId]);

        $customerGroupId = CustomerGroup::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;

        $customerArr = Customer::factory()->make([
            'company_id' => $companyId,
            'customer_group_id' => $customerGroupId,
        ])->toArray();

        $customerAddressArr = [];

        $customerAddressesArr = CustomerAddress::factory()->make([
            'company_id' => $companyId,
        ])->toArray();

        array_push($customerAddressArr, $customerAddressesArr);

        $picArr = Profile::factory()->make()->toArray();
        $picArr['name'] = strtolower($picArr['first_name'].$picArr['last_name']).$this->randomGenerator->generateNumber(1, 999);
        $picArr['email'] = $picArr['name'].'@something.com';
        $picArr['password'] = $user['password'];
        $picArr['status'] = $customerArr['status'];

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
            'company_id' => $companyId,
            'customer_id' => $result->id,
            'address' => $customerAddressArr[0]['address'],
            'city' => $customerAddressArr[0]['city'],
            'contact' => $customerAddressArr[0]['contact'],
            'is_main' => $customerAddressArr[0]['is_main'],
            'remarks' => $customerAddressArr[0]['remarks'],
        ]);

        // $this->assertDatabaseHas('profiles', [
        //     'first_name' => $picArr['first_name'],
        //     'last_name' => $picArr['last_name'],
        //     'status' => RecordStatus::ACTIVE->value,
        // ]);
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
