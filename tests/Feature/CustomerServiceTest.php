<?php

namespace Tests\Feature;

use App\Models\Cash;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use App\Services\CustomerService;
use App\Services\CustomerGroupService;
use App\Actions\RandomGenerator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class CustomerServiceTest extends TestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CustomerService::class);
        $this->CustomerGroupService = app(CustomerGroupService::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_read()
    {
        $selectedCompanyId = Company::inRandomOrder()->get()[0]->id;
        session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), Hashids::encode($selectedCompanyId));
        $response = $this->service->read();

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
    }
    
    public function test_create()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;

        $faker = \Faker\Factory::create('id_ID');

        $this->CustomerGroupService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 365),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(0, 1),
            (new RandomGenerator())->generateNumber(0, 2),
            (new RandomGenerator())->generateNumber(1, 9999),
            null,
            Cash::inRandomOrder()->get()[0]->id
        );

        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $is_member = (new RandomGenerator())->generateNumber(0, 1);
        $customer_group_id = CustomerGroup::inRandomOrder()->get()[0]->id;
        $zone = $this->faker->city;
        $max_open_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_outstanding_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_invoice_age = (new RandomGenerator())->generateNumber(1, 9999);
        $payment_term = (new RandomGenerator())->generateNumber(1, 365);
        $tax_id = (new RandomGenerator())->generateNumber(0, 1);
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $customer_addresses = [];
        $nCount = (new RandomGenerator())->generateNumber(1,5);
        
        for ($i = 0; $i < $nCount; $i++) {

            array_push($customer_addresses, array (
                'company_id' => $company_id,
                'customer_id' => null,
                'address' => $faker->address(),
                'city' => $faker->city(),
                'contact' => $faker->phoneNumber(),
                'address_remarks' => ''
            ));
        }

        $customer = $this->service->create(
            $company_id,
            $code,
            $name,
            $is_member,
            $customer_group_id,
            $zone,
            $max_open_invoice,
            $max_outstanding_invoice,
            $max_invoice_age,
            $payment_term,
            $tax_id,
            $remarks,
            $status,
            $customer_addresses
        );

        $this->assertDatabaseHas('customers', [
            'company_id' => $company_id,
            'customer_group_id' => $customer_group_id,
            'code' => $code,
            'is_member' => $is_member,
            'name' => $name,
            'zone' => $zone,
            'max_open_invoice' => $max_open_invoice,
            'max_outstanding_invoice' => $max_outstanding_invoice,
            'max_invoice_age' => $max_invoice_age,
            'payment_term' => $payment_term,
            'tax_id' => $tax_id,
            'remarks' => $remarks,
            'status' => $status
        ]);

        foreach ($customer_addresses as $customer_address) {
            $this->assertDatabaseHas('customer_addresses', [
                'company_id' => $customer_address['company_id'],
                'customer_id' => Hashids::decode($customer)[0],
                'address' => $customer_address['address'],
                'city' => $customer_address['city'],
                'contact' => $customer_address['contact'],
                'remarks' => $customer_address['address_remarks']
            ]);
        }
    }

    public function test_update()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;

        $faker = \Faker\Factory::create('id_ID');

        $this->CustomerGroupService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 365),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(1, 9999),
            (new RandomGenerator())->generateNumber(0, 1),
            (new RandomGenerator())->generateNumber(0, 2),
            (new RandomGenerator())->generateNumber(1, 9999),
            null,
            Cash::inRandomOrder()->get()[0]->id
        );

        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name();
        $is_member = (new RandomGenerator())->generateNumber(0, 1);
        $customer_group_id = CustomerGroup::inRandomOrder()->get()[0]->id;
        $zone = $this->faker->city;
        $max_open_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_outstanding_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_invoice_age = (new RandomGenerator())->generateNumber(1, 9999);
        $payment_term = (new RandomGenerator())->generateNumber(1, 365);
        $tax_id = (new RandomGenerator())->generateNumber(0, 1);
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $customer_addresses = [];
        $nCount = (new RandomGenerator())->generateNumber(1,5);
        
        for ($i = 0; $i < $nCount; $i++) {
            array_push($customer_addresses, array (
                'company_id' => $company_id,
                'customer_id' => null,
                'address' => $faker->address(),
                'city' => $faker->city(),
                'contact' => $faker->phoneNumber(),
                'address_remarks' => ''
            ));
        }

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $is_member,
            $customer_group_id,
            $zone,
            $max_open_invoice,
            $max_outstanding_invoice,
            $max_invoice_age,
            $payment_term,
            $tax_id,
            $remarks,
            $status,
            $customer_addresses
        );

        $id = Hashids::decode($response)[0];
        $company_id_new = Company::inRandomOrder()->get()[0]->id;
        $code_new = (new RandomGenerator())->generateNumber(1, 9999);
        $name_new = $this->faker->name();
        $is_member_new = (new RandomGenerator())->generateNumber(0, 1);
        $customer_group_id_new = CustomerGroup::inRandomOrder()->get()[0]->id;
        $zone_new = $this->faker->city;
        $max_open_invoice_new = (new RandomGenerator())->generateNumber(1, 9999);
        $max_outstanding_invoice_new = (new RandomGenerator())->generateNumber(1, 9999);
        $max_invoice_age_new = (new RandomGenerator())->generateNumber(1, 9999);
        $payment_term_new = (new RandomGenerator())->generateNumber(1, 365);
        $tax_id_new = (new RandomGenerator())->generateNumber(0, 1);
        $remarks_new = null;
        $status_new = (new RandomGenerator())->generateNumber(0, 1);

        $customer_addresses_new = CustomerAddress::where('customer_id', '=', $id)->get();
        $nCount = count($customer_addresses_new);
        
        for ($i = 0; $i < $nCount; $i++) {

            $customer_addresses_new[$i]->company_id = $company_id;
            $customer_addresses_new[$i]->customer_id = $id;
            $customer_addresses_new[$i]->address = $faker->unique()->numberBetween(001, 99999);
            $customer_addresses_new[$i]->city = $faker->city();
            $customer_addresses_new[$i]->contact = $faker->phoneNumber();
            $customer_addresses_new[$i]->address_remarks = $faker->word();
        }

        $response = $this->service->update(
            $id, 
            $company_id_new,
            $code_new,
            $name_new,
            $is_member_new,
            $customer_group_id_new,
            $zone_new,
            $max_open_invoice_new,
            $max_outstanding_invoice_new,
            $max_invoice_age_new,
            $payment_term_new,
            $tax_id_new,
            $remarks_new,
            $status_new,
            $customer_addresses_new
        );

        $this->assertDatabaseHas('customers', [
            'company_id' => $company_id_new,
            'customer_group_id' => $customer_group_id_new,
            'code' => $code_new,
            'name' => $name_new,
            'is_member' => $is_member_new,
            'zone' => $zone_new,
            'max_open_invoice' => $max_open_invoice_new,
            'max_outstanding_invoice' => $max_outstanding_invoice_new,
            'max_invoice_age' => $max_invoice_age_new,
            'payment_term' => $payment_term_new,
            'remarks' => $remarks_new,
            'status' => $status_new,
        ]);
    }

    public function test_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name();
        $is_member = (new RandomGenerator())->generateNumber(0, 1);
        $customer_group_id = CustomerGroup::inRandomOrder()->get()[0]->id;
        $zone = $this->faker->city;
        $max_open_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_outstanding_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_invoice_age = (new RandomGenerator())->generateNumber(1, 9999);
        $payment_term = (new RandomGenerator())->generateNumber(1, 365);
        $tax_id = (new RandomGenerator())->generateNumber(0, 1);
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $customer_addresses = [];

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $is_member,
            $customer_group_id,
            $zone,
            $max_open_invoice,
            $max_outstanding_invoice,
            $max_invoice_age,
            $payment_term,
            $tax_id,
            $remarks,
            $status,
            $customer_addresses
        );
        $rId = Hashids::decode($response)[0];

        $response = $this->service->delete($rId);
        $deleted_at = Customer::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('customers', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
}
