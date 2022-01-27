<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Actions\RandomGenerator;
use App\Services\SupplierService;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupplierServiceTest extends TestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SupplierService::class);
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
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $payment_term_type = (new RandomGenerator())->generateNumber(0, 50);
        $contact = $this->faker->e164PhoneNumber;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $taxable_enterprise = (new RandomGenerator())->generateNumber(0, 1);
        $tax_id = $this->faker->name;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $supplier_products = [];
        $product_count = Product::count();
        $product_count = (new RandomGenerator())->generateNumber(0, $product_count);
        $products = Product::all()->random($product_count);
        foreach ($products as $product) {
            array_push($supplier_products, new SupplierProduct(array (
                'company_id' => $company_id,
                'supplier_id' => null,
                'product_id' => $product['id']
            )));
        }
        
        $result = $this->service->create(
            $company_id,
            $code,
            $name,
            $payment_term_type,
            $contact,
            $address,
            $city,
            $taxable_enterprise,
            $tax_id,
            $remarks,
            $status,
            $supplier_products
        );
        $supplier_id = Hashids::decode($result)[0];

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

        foreach ($products as $product) {
            $this->assertDatabaseHas('supplier_products', [
                'company_id' => $company_id,
                'supplier_id' => $supplier_id,
                'product_id' => $product->id
            ]);
        }
    }

    public function test_update()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $payment_term_type = (new RandomGenerator())->generateNumber(0, 50);
        $contact = $this->faker->e164PhoneNumber;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $taxable_enterprise = (new RandomGenerator())->generateNumber(0, 1);
        $tax_id = $this->faker->name;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $supplier_products = [];
        $product_count = Product::count();
        $product_count = (new RandomGenerator())->generateNumber(0, $product_count);
        $products = Product::all()->random($product_count);
        foreach ($products as $product) {
            array_push($supplier_products, array (
                'company_id' => $company_id,
                'supplier_id' => null,
                'product_id' => $product['id']
            ));
        }

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $payment_term_type,
            $contact,
            $address,
            $city,
            $taxable_enterprise,
            $tax_id,
            $remarks,
            $status,
            $supplier_products
        );

        $id = Hashids::decode($response)[0];
        $company_id_new = Company::inRandomOrder()->get()[0]->id;
        $code_new = (new RandomGenerator())->generateNumber(1, 9999);
        $name_new = $this->faker->name;
        $payment_term_type_new = (new RandomGenerator())->generateNumber(0, 50);
        $contact_new = $this->faker->e164PhoneNumber;
        $address_new = $this->faker->address;
        $city_new = $this->faker->city;
        $taxable_enterprise_new = (new RandomGenerator())->generateNumber(0, 1);
        $tax_id_new = $this->faker->name;
        $remarks_new = null;
        $status_new = (new RandomGenerator())->generateNumber(0, 1);

        $supplier_products_new = [];
        $supplier_products_new = SupplierProduct::where('supplier_id', '=', $id)->get();;
        $nCount = count($supplier_products_new);
        $products = Product::all()->random($nCount);
        // foreach ($products as $product) {
        //     array_push($supplier_products_new, array(
        //         'company_id' => $company_id,
        //         'supplier_id' => null,
        //         'product_id' => $product['id']
        //     ));
        // }

        for ($i = 0; $i < $nCount; $i++) {
            $products[$i]->company_id = $company_id;
            $products[$i]->supplier_id = $id;
            $products[$i]->product_id = $products[$i]['id'];
        }
        
        $response = $this->service->update(
            $id, 
            $company_id_new,
            $code_new,
            $name_new,
            $payment_term_type_new,
            $contact_new,
            $address_new,
            $city_new,
            $taxable_enterprise_new,
            $tax_id_new,
            $remarks_new,
            $status_new,
            $supplier_products_new
        );

        $this->assertDatabaseHas('suppliers', [
            'id' => $id,
            'company_id' => $company_id_new,
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

        // foreach ($products as $product) {
        //     $this->assertDatabaseHas('supplier_products', [
        //         'company_id' => $company_id,
        //         'supplier_id' => $supplier_id,
        //         'product_id' => $product->id
        //     ]);
        // }

        for ($i = 0; $i < $nCount; $i++) {
            $this->assertDatabaseHas('supplier_products', [
                'company_id' => $supplier_products_new[$i]->company_id,
                'supplier_id' => $supplier_products_new[$i]->supplier_id,
                'product_id' => $supplier_products_new[$i]->product_id,
            ]);
        }
    }

    public function test_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $payment_term_type = (new RandomGenerator())->generateNumber(0, 50);
        $contact = $this->faker->e164PhoneNumber;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $taxable_enterprise = (new RandomGenerator())->generateNumber(0, 1);
        $tax_id = $this->faker->name;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);        
        $supplier_products = [];

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $payment_term_type,
            $contact,
            $address,
            $city,
            $taxable_enterprise,
            $tax_id,
            $remarks,
            $status,
            $supplier_products
        );
        $rId = Hashids::decode($response)[0];

        $response = $this->service->delete($rId);
        $deleted_at = Supplier::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('suppliers', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
}