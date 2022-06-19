<?php

namespace Tests\Feature\Service;

use App\Actions\RandomGenerator;
use App\Services\SupplierService;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\SupplierTableSeeder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\ServiceTestCase;
use Vinkla\Hashids\Facades\Hashids;

class SupplierServiceTest extends ServiceTestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SupplierService::class);

        if (User::count() == 0)
            $this->artisan('db:seed', ['--class' => 'UserTableSeeder']);

        if (User::has('companies')->count() == 0) {
            $companyPerUser = 3;
            $companySeeder = new CompanyTableSeeder();
            $companySeeder->callWith(CompanyTableSeeder::class, [$companyPerUser]);    
        }

        if (Supplier::count() == 0) {
            $supplierPerCompany = 3;

            $supplierSeeder = new SupplierTableSeeder();
            $supplierSeeder->callWith(SupplierTableSeeder::class, [$supplierPerCompany]);
        }
        
        $this->selectedCompanyId = Company::inRandomOrder()->get()[0]->id;
    }

    public function test_call_read()
    {
        $response = $this->service->read($this->selectedCompanyId, '', true, 10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }


    public function test_call_read_with_negative_value_in_perpage_param()
    {
        $response = $this->service->read($this->selectedCompanyId, '', true, -10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_read_without_pagination()
    {
        $response = $this->service->read($this->selectedCompanyId, '', false, 10);

        $this->assertInstanceOf(Collection::class, $response);
    }

    public function test_create()
    {
        $paymentTermType = ['PIA','NET30','EOM','COD','CND'];

        shuffle($paymentTermType);

        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $payment_term_type = $paymentTermType[0];
        $payment_term = 0;
        $contact = $this->faker->e164PhoneNumber;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $taxable_enterprise = $this->faker->boolean();
        $tax_id = (new RandomGenerator())->generateNumber(0, 1999);
        $remarks = $this->faker->word;
        $status = $this->faker->boolean();

        $poc = [
            'name' => $this->faker->name,
            'email' => $this->faker->email 
        ];

        $products = [];

        $this->service->create(
            $company_id,
            $code,
            $name,
            $payment_term_type,
            $payment_term,
            $contact,
            $address,
            $city,
            $taxable_enterprise,
            $tax_id,
            $remarks,
            $status,
            $poc,
            $products
        );

        $this->assertDatabaseHas('suppliers', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'payment_term_type' => $payment_term_type,
            'contact' => $contact,
            'address' => $address,
            'city' => $city,
            'taxable_enterprise' => $taxable_enterprise,
            'tax_id' => $tax_id,
            'remarks' => $remarks,
            'status' => $status
        ]);
    }

    public function test_update()
    {
        $paymentTermType = ['PIA','NET30','EOM','COD','CND'];

        shuffle($paymentTermType);

        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $payment_term_type = $paymentTermType[0];
        $payment_term = 0;
        $contact = $this->faker->e164PhoneNumber;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $taxable_enterprise = $this->faker->boolean();
        $tax_id = $this->faker->name;
        $remarks = null;
        $status = $this->faker->boolean();

        $poc = [
            'name' => $this->faker->name,
            'email' => $this->faker->email 
        ];

        $products = [];

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $payment_term_type,
            $payment_term,
            $contact,
            $address,
            $city,
            $taxable_enterprise,
            $tax_id,
            $remarks,
            $status,
            $poc,
            $products
        );

        $rId = Hashids::decode($response->hId)[0];

        shuffle($paymentTermType);

        $code_new = (new RandomGenerator())->generateNumber(1, 9999);
        $name_new = $this->faker->name;
        $payment_term_type_new = $paymentTermType[0];
        $payment_term_new = 30;
        $contact_new = $this->faker->e164PhoneNumber;
        $address_new = $this->faker->address;
        $city_new = $this->faker->city;
        $taxable_enterprise_new = $this->faker->boolean();
        $tax_id_new = $this->faker->name;
        $remarks_new = $this->faker->word;
        $status_new = $this->faker->boolean();

        $poc_new = [];
        $products_new = [];

        $response = $this->service->update(
            $rId, 
            $company_id,
            $code_new,
            $name_new,
            $payment_term_type_new,
            $payment_term_new,
            $contact_new,
            $address_new,
            $city_new,
            $taxable_enterprise_new,
            $tax_id_new,
            $remarks_new,
            $status_new,
            $poc_new,
            $products_new
        );

        $this->assertDatabaseHas('suppliers', [
            'id' => $rId,
            'company_id' => $company_id,
            'code' => $code_new,
            'name' => $name_new,
            'payment_term_type' => $payment_term_type_new,
            'contact' => $contact_new,
            'address' => $address_new,
            'city' => $city_new,
            'taxable_enterprise' => $taxable_enterprise_new,
            'tax_id' => $tax_id_new,
            'remarks' => $remarks_new,
            'status' => $status_new
        ]);
    }

    public function test_delete()
    {
        $paymentTermType = ['PIA','NET30','EOM','COD','CND'];

        shuffle($paymentTermType);

        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $payment_term_type = $paymentTermType[0];
        $payment_term = 0;
        $contact = $this->faker->e164PhoneNumber;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $taxable_enterprise = $this->faker->boolean();
        $tax_id = $this->faker->name;
        $remarks = $this->faker->word;
        $status = $this->faker->boolean();

        $poc = [
            'name' => $this->faker->name,
            'email' => $this->faker->email 
        ];

        $products = [];

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $payment_term_type,
            $payment_term,
            $contact,
            $address,
            $city,
            $taxable_enterprise,
            $tax_id,
            $remarks,
            $status,
            $poc,
            $products
        );

        $rId = Hashids::decode($response->hId)[0];

        $response = $this->service->delete($rId);
        
        $this->assertSoftDeleted('suppliers', [
            'id' => $rId
        ]);
    }

    public function test_call_delete_with_nonexistence_id()
    {
        $max_int = (2147483647);
        
        $response = $this->service->delete($max_int);

        $this->assertFalse($response);
    }
}