<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class CustomerAPICreateTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_api_call_store_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(CustomerGroup::factory()->count(3))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $arr_customer_address_id = [];
        $arr_customer_address_address = [];
        $arr_customer_address_city = [];
        $arr_customer_address_contact = [];
        $arr_customer_address_is_main = [];
        $arr_customer_address_remarks = [];

        $addressCount = $this->faker->numberBetween(1, 5);
        $mainAddressIdx = $this->faker->numberBetween(0, $addressCount - 1);
        for ($i = 0; $i < 3; $i++) {
            $customerAddress = CustomerAddress::factory()->make();

            array_push($arr_customer_address_id, '');
            array_push($arr_customer_address_address, $customerAddress->address);
            array_push($arr_customer_address_city, $customerAddress->city);
            array_push($arr_customer_address_contact, $customerAddress->contact);
            array_push($arr_customer_address_is_main, $i == $mainAddressIdx ? true : false);
            array_push($arr_customer_address_remarks, $customerAddress->remarks);
        }

        $customerArr = Customer::factory()->setStatusActive()->make([
            'company_id' => Hashids::encode($company->id),
            'customer_group_id' => Hashids::encode($customerGroup->id),
            'arr_customer_address_id' => $arr_customer_address_id,
            'arr_customer_address_address' => $arr_customer_address_address,
            'arr_customer_address_city' => $arr_customer_address_city,
            'arr_customer_address_contact' => $arr_customer_address_contact,
            'arr_customer_address_is_main' => $arr_customer_address_is_main,
            'arr_customer_address_remarks' => $arr_customer_address_remarks,
        ])->toArray();

        $customerArr['pic_create_user'] = random_int(0, 1);
        if ($customerArr['pic_create_user'] == 1) {
            $customerArr['pic_contact_person_name'] = $this->faker->name();
            $customerArr['pic_email'] = $this->faker->email();
            $customerArr['pic_password'] = '123456';
        }

        $api = $this->json('POST', route('api.post.db.customer.customer.save'), $customerArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('customers', [
            'company_id' => Hashids::decode($customerArr['company_id'])[0],
            'customer_group_id' => Hashids::decode($customerArr['customer_group_id']),
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

        for ($i = 0; $i < count($arr_customer_address_id); $i++) {
            $this->assertDatabaseHas('customer_addresses', [
                'company_id' => $company->id,
                'address' => $arr_customer_address_address[$i],
                'city' => $arr_customer_address_city[$i],
                'contact' => $arr_customer_address_contact[$i],
                'is_main' => $arr_customer_address_is_main[$i],
                'remarks' => $arr_customer_address_remarks[$i],
            ]);
        }
    }

    public function test_customer_api_call_store_with_nonexistance_customer_group_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        // bikin user, role yang namanya developer di tambahin ke user, bikin company, customer
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(CustomerGroup::factory()->count(3))
                    )->create();

        $this->actingAs($user);

        // ambil data company
        $company = $user->companies()->inRandomOrder()->first();

        // bikin array kosong
        $arr_customer_address_id = [];
        $arr_customer_address_address = [];
        $arr_customer_address_city = [];
        $arr_customer_address_contact = [];
        $arr_customer_address_is_main = [];
        $arr_customer_address_remarks = [];

        // looping sebanyak 0 sampe kurang dari 3
        for ($i = 0; $i < 3; $i++) {
            // masukin data di bawah ke array kosong di atas
            // data di ambil dari $customerAddress yang bikin factory
            $customerAddress = CustomerAddress::factory()->make();
            array_push($arr_customer_address_id, '');
            array_push($arr_customer_address_address, $customerAddress->address);
            array_push($arr_customer_address_city, $customerAddress->city);
            array_push($arr_customer_address_contact, $customerAddress->contact);
            array_push($arr_customer_address_is_main, $customerAddress->is_main);
            array_push($arr_customer_address_remarks, $customerAddress->remarks);
        }

        // bikin customer pake factiry yang statusnya dibikin aktif di tambahin data di bawah trs jadiin array
        $customerArr = Customer::factory()->setStatusActive()->make([
            'company_id' => Hashids::encode($company->id),
            'customer_group_id' => Hashids::encode(CustomerGroup::max('id') + 1),
            'arr_customer_address_id' => $arr_customer_address_id,
            'arr_customer_address_address' => $arr_customer_address_address,
            'arr_customer_address_city' => $arr_customer_address_city,
            'arr_customer_address_contact' => $arr_customer_address_contact,
            'arr_customer_address_is_main' => $arr_customer_address_is_main,
            'arr_customer_address_remarks' => $arr_customer_address_remarks,
        ])->toArray();

        // pic_create_user datanya dari random integer 0 -1
        $customerArr['pic_create_user'] = random_int(0, 1);
        // jika pic_create_user hasilnya 1 maka
        // pic_contact_person_name diisi hasil faker nama
        // pic_email diisi hasil faker email
        // pic_password diisi 123456
        if ($customerArr['pic_create_user'] == 1) {
            $customerArr['pic_contact_person_name'] = $this->faker->name();
            $customerArr['pic_email'] = $this->faker->email();
            $customerArr['pic_password'] = '123456';
        }

        $api = $this->json('POST', route('api.post.db.customer.customer.save'), $customerArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_customer_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(CustomerGroup::factory()->count(3))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
            $customerGroup = $company->customerGroups()->inRandomOrder()->first();

            $customer = Customer::factory()->for($company)->for($customerGroup);
            for ($i = 0; $i < 3; $i++) {
                $customer = $customer->has(CustomerAddress::factory()->for($company));
            }
            $customer->create();
        }

        $arr_customer_address_id = [];
        $arr_customer_address_address = [];
        $arr_customer_address_city = [];
        $arr_customer_address_contact = [];
        $arr_customer_address_is_main = [];
        $arr_customer_address_remarks = [];

        for ($i = 0; $i < 3; $i++) {
            $customerAddress = CustomerAddress::factory()->make();
            array_push($arr_customer_address_id, '');
            array_push($arr_customer_address_address, $customerAddress->address);
            array_push($arr_customer_address_city, $customerAddress->city);
            array_push($arr_customer_address_contact, $customerAddress->contact);
            array_push($arr_customer_address_is_main, $customerAddress->is_main);
            array_push($arr_customer_address_remarks, $customerAddress->remarks);
        }

        $customerArr = Customer::factory()->make()->toArray();
        $customerArr = array_merge($customerArr, [
            'company_id' => Hashids::encode($company->id),
            'code' => $company->customers()->inRandomOrder()->first()->code,
            'customer_group_id' => Hashids::encode(CustomerGroup::where('company_id', '=', $company->id)->inRandomOrder()->first()->id),
            'arr_customer_address_id' => $arr_customer_address_id,
            'arr_customer_address_address' => $arr_customer_address_address,
            'arr_customer_address_city' => $arr_customer_address_city,
            'arr_customer_address_contact' => $arr_customer_address_contact,
            'arr_customer_address_is_main' => $arr_customer_address_is_main,
            'arr_customer_address_remarks' => $arr_customer_address_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.customer.customer.save'), $customerArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_customer_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(CustomerGroup::factory())->count(3))
                    ->has(Company::factory()->setStatusActive()
                        ->has(CustomerGroup::factory()->count(3)))
                    ->create();

        $this->actingAs($user);

        $company_1 = $user->companies[0];

        for ($x = 0; $x < 3; $x++) {
            $customerGroup = $company_1->customerGroups()->inRandomOrder()->first();

            $customer = Customer::factory()->for($company_1)->for($customerGroup);
            for ($i = 0; $i < 3; $i++) {
                $customer = $customer->has(CustomerAddress::factory()->for($company_1));
            }
            $customer->create();
        }

        $company_2 = $user->companies[1];

        for ($x = 0; $x < 3; $x++) {
            $customerGroup = $company_2->customerGroups()->inRandomOrder()->first();

            $customer = Customer::factory()->for($company_2)->for($customerGroup);
            for ($i = 0; $i < 3; $i++) {
                $customer = $customer->has(CustomerAddress::factory()->for($company_2));
            }
            $customer->create();
        }

        $arr_customer_address_id = [];
        $arr_customer_address_address = [];
        $arr_customer_address_city = [];
        $arr_customer_address_contact = [];
        $arr_customer_address_is_main = [];
        $arr_customer_address_remarks = [];

        $addressCount = $this->faker->numberBetween(1, 5);
        $mainAddressIdx = $this->faker->numberBetween(0, $addressCount - 1);
        for ($i = 0; $i < $addressCount; $i++) {
            $customerAddress = CustomerAddress::factory()->make();

            array_push($arr_customer_address_id, '');
            array_push($arr_customer_address_address, $customerAddress->address);
            array_push($arr_customer_address_city, $customerAddress->city);
            array_push($arr_customer_address_contact, $customerAddress->contact);
            array_push($arr_customer_address_is_main, $i == $mainAddressIdx ? true : false);
            array_push($arr_customer_address_remarks, $customerAddress->remarks);
        }

        $customerArr = Customer::factory()->make()->toArray();
        $customerArr = array_merge($customerArr, [
            'company_id' => Hashids::encode($company_1->id),
            'code' => $company_2->customers()->inRandomOrder()->first()->code,
            'customer_group_id' => Hashids::encode($company_1->customerGroups()->inRandomOrder()->first()->id),
            'arr_customer_address_id' => $arr_customer_address_id,
            'arr_customer_address_address' => $arr_customer_address_address,
            'arr_customer_address_city' => $arr_customer_address_city,
            'arr_customer_address_contact' => $arr_customer_address_contact,
            'arr_customer_address_is_main' => $arr_customer_address_is_main,
            'arr_customer_address_remarks' => $arr_customer_address_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.customer.customer.save'), $customerArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('customers', [
            'company_id' => $company_1->id,
            'customer_group_id' => Hashids::decode($customerArr['customer_group_id']),
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

        for ($i = 0; $i < $addressCount; $i++) {
            $this->assertDatabaseHas('customer_addresses', [
                'company_id' => $company_1->id,
                'address' => $arr_customer_address_address[$i],
                'city' => $arr_customer_address_city[$i],
                'contact' => $arr_customer_address_contact[$i],
                'is_main' => $arr_customer_address_is_main[$i],
                'remarks' => $arr_customer_address_remarks[$i],
            ]);
        }
    }

    public function test_customer_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->create();

        $this->actingAs($user);

        $customerArr = [];
        $api = $this->json('POST', route('api.post.db.customer.customer.save'), $customerArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
