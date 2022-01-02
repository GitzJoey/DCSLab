<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductGroup;
use App\Services\BrandService;
use App\Actions\RandomGenerator;
use App\Models\ProductUnit;
use App\Services\ProductService;
use App\Services\SupplierService;
use Vinkla\Hashids\Facades\Hashids;
use App\Services\ProductGroupService;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductServiceTest extends TestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ProductService::class);
        $this->productGroupService = app(ProductGroupService::class);
        $this->brandService = app(BrandService::class);
        $this->supplierService = app(SupplierService::class);
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

    public function test_read_product()
    {
        $selectedCompanyId = Company::inRandomOrder()->get()[0]->id;
        session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), Hashids::encode($selectedCompanyId));
        $response = $this->service->read_product();

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
    }

    public function test_read_service()
    {
        $selectedCompanyId = Company::inRandomOrder()->get()[0]->id;
        session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), Hashids::encode($selectedCompanyId));
        $response = $this->service->read_service();

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
    }
    
    public function test_create()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;

        $faker = \Faker\Factory::create('id_ID');
        
        $this->productGroupService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            (new RandomGenerator())->generateNumber(1, 3)
        );

        $this->brandService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
        );

        $this->supplierService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            $faker->creditCardType(),
            $faker->e164PhoneNumber(),
            $faker->e164PhoneNumber(),
            $faker->address(),
            $faker->city(),
            (new RandomGenerator())->generateNumber(0, 1),
            null,
            (new RandomGenerator())->generateNumber(0, 1),
            []
        );

        $code = (new RandomGenerator())->generateNumber(1,9999);
        $product_group_id = ProductGroup::inRandomOrder()->get()[0]->id;
        $brand_id = Brand::inRandomOrder()->get()[0]->id;
        $name = $this->faker->name;
        $tax_status = (new RandomGenerator())->generateNumber(1,9999);
        $supplier_id = Supplier::inRandomOrder()->get()[0]->id; 
        $remarks = null;
        $point = (new RandomGenerator())->generateNumber(1,9999);
        $use_serial_number = (new RandomGenerator())->generateNumber(0, 1);
        $has_expiry_date = (new RandomGenerator())->generateNumber(0, 1);
        $product_type = (new RandomGenerator())->generateNumber(1, 4);
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $product_units = [];
        $nCount = (new RandomGenerator())->generateNumber(1,5);
        $units = Unit::all()->random($nCount);
        
        $conv_value = 1;
        $is_primary_unit = $faker->numberBetween(0, $nCount);
        for ($i = 0; $i < $nCount; $i++) {
            $conv_value = $i === 0 ? $conv_value : $conv_value * $faker->numberBetween(2, 5);

            array_push($product_units, array (
                'company_id' => $company_id,
                'product_id' => null,
                'code' => $faker->unique()->numberBetween(001, 99999),
                'unit_id' => $units[$i]->id,
                'conv_value' => $conv_value,
                'is_base' => $conv_value === 1 ? 1 : 0,
                'is_primary_unit' => $is_primary_unit === $i ? 1 : 0,
                'remarks' => ''
            ));
        }

        $product = $this->service->create(
            $company_id,
            $code,
            $product_group_id,
            $brand_id,
            $name,
            $tax_status,
            $supplier_id,
            $remarks,
            $point,
            $use_serial_number,
            $has_expiry_date,
            $product_type,
            $status,
            $product_units
        );
    
        $this->assertDatabaseHas('products', [
            'company_id' => $company_id,
            'code' => $code,
            'product_group_id' => $product_group_id,
            'brand_id' => $brand_id,
            'name' => $name,
            'tax_status' => $tax_status,
            'supplier_id' => $supplier_id,
            'remarks' => $remarks,
            'point' => $point,
            'use_serial_number' => $use_serial_number,
            'has_expiry_date' => $has_expiry_date,
            'product_type' => $product_type,
            'status' => $status
        ]);

        foreach ($product_units as $product_unit) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $product_unit['company_id'],
                'product_id' => Hashids::decode($product)[0],
                'code' => $product_unit['code'],
                'unit_id' => $product_unit['unit_id'],
                'conversion_value' => $product_unit['conv_value'],
                'is_base' => $product_unit['is_base'],
                'is_primary_unit' => $product_unit['is_primary_unit'],
                'remarks' => $product_unit['remarks']
            ]);
          }
    }

    public function test_update()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;

        $faker = \Faker\Factory::create('id_ID');

        $this->productGroupService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            (new RandomGenerator())->generateNumber(1, 3)
        );

        $this->brandService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
        );

        $this->supplierService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            $faker->creditCardType(),
            $faker->e164PhoneNumber(),
            $faker->e164PhoneNumber(),
            $faker->address(),
            $faker->city(),
            (new RandomGenerator())->generateNumber(0, 1),
            null,
            (new RandomGenerator())->generateNumber(0, 1),
            []
        );

        $code = (new RandomGenerator())->generateNumber(1,9999);
        $product_group_id = ProductGroup::inRandomOrder()->get()[0]->id;
        $brand_id = Brand::inRandomOrder()->get()[0]->id;
        $name = $this->faker->name;
        $tax_status = (new RandomGenerator())->generateNumber(1,9999);
        $supplier_id = Supplier::inRandomOrder()->get()[0]->id; 
        $remarks = null;
        $point = (new RandomGenerator())->generateNumber(1,9999);
        $use_serial_number = (new RandomGenerator())->generateNumber(0, 1);
        $has_expiry_date = (new RandomGenerator())->generateNumber(0, 1);
        $product_type = (new RandomGenerator())->generateNumber(1, 4);
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $product_units = [];
        $nCount = (new RandomGenerator())->generateNumber(1,5);
        $units = Unit::all()->random($nCount);
        
        $conv_value = 1;
        $is_primary_unit = $faker->numberBetween(0, $nCount);
        for ($i = 0; $i < $nCount; $i++) {
            $conv_value = $i === 0 ? $conv_value : $conv_value * $faker->numberBetween(2, 5);

            array_push($product_units, array (
                'company_id' => $company_id,
                'product_id' => null,
                'code' => $faker->unique()->numberBetween(001, 99999),
                'unit_id' => $units[$i]->id,
                'conv_value' => $conv_value,
                'is_base' => $conv_value === 1 ? 1 : 0,
                'is_primary_unit' => $is_primary_unit === $i ? 1 : 0,
                'remarks' => $faker->word()
            ));
        }

        $product = $this->service->create(
            $company_id,
            $code,
            $product_group_id,
            $brand_id,
            $name,
            $tax_status,
            $supplier_id,
            $remarks,
            $point,
            $use_serial_number,
            $has_expiry_date,
            $product_type,
            $status,
            $product_units
        );

        $product_id = Hashids::decode($product)[0];
        $company_id_new = Company::inRandomOrder()->get()[0]->id;
        $code_new = (new RandomGenerator())->generateNumber(1,9999);
        $product_group_id_new = ProductGroup::inRandomOrder()->get()[0]->id;
        $brand_id_new = Brand::inRandomOrder()->get()[0]->id;
        $name_new = $this->faker->name;
        $tax_status_new = (new RandomGenerator())->generateNumber(1,9999);
        $supplier_id_new = Supplier::inRandomOrder()->get()[0]->id; 
        $remarks_new = $faker->word();
        $point_new = (new RandomGenerator())->generateNumber(1,9999);
        $use_serial_number_new = (new RandomGenerator())->generateNumber(0, 1);
        $has_expiry_date_new = (new RandomGenerator())->generateNumber(0, 1);
        $product_type_new = (new RandomGenerator())->generateNumber(1, 4);
        $status_new = (new RandomGenerator())->generateNumber(0, 1);

        $product_units_new = ProductUnit::where('product_id', '=', $product_id)->get();
        $nCount = count($product_units_new);
        $units = Unit::all()->random($nCount);
        
        $conv_value = 1;
        $is_primary_unit = $faker->numberBetween(0, $nCount);
        for ($i = 0; $i < $nCount; $i++) {
            $conv_value = $i === 0 ? $conv_value : $conv_value * $faker->numberBetween(2, 5);

            $product_units_new[$i]->company_id = $company_id_new;
            $product_units_new[$i]->product_id = $product_id;
            $product_units_new[$i]->code = $faker->unique()->numberBetween(001, 99999);
            $product_units_new[$i]->unit_id = $units[$i]->id;
            $product_units_new[$i]->conv_value = $conv_value;
            $product_units_new[$i]->is_base = $conv_value === 1 ? 1 : 0;
            $product_units_new[$i]->is_primary_unit = $is_primary_unit === $i ? 1 : 0;
            $product_units_new[$i]->remarks = $faker->word();
        }

        $response = $this->service->update(
            $product_id,
            $company_id_new,
            $code_new,
            $product_group_id_new,
            $brand_id_new,
            $name_new,
            $tax_status_new,
            $supplier_id_new,
            $remarks_new,
            $point_new,
            $use_serial_number_new,
            $has_expiry_date_new,
            $product_type_new,
            $status_new,
            $product_units_new
        );

        $this->assertDatabaseHas('products', [
            'id' => $product_id,
            'company_id' => $company_id_new,
            'code' => $code_new,
            'product_group_id' => $product_group_id_new,
            'brand_id' => $brand_id_new,
            'name' => $name_new,
            'tax_status' => $tax_status_new,
            'supplier_id' => $supplier_id_new,
            'remarks' => $remarks_new,
            'point' => $point_new,
            'use_serial_number' => $use_serial_number_new,
            'has_expiry_date' => $has_expiry_date_new,
            'product_type' => $product_type_new,
            'status' => $status_new
        ]);

        for ($i = 0; $i < $nCount; $i++) {
            $this->assertDatabaseHas('product_units', [
                'id' => $product_units_new[$i]->id,
                'company_id' => $product_units_new[$i]->company_id,
                'product_id' => $product_units_new[$i]->product_id,
                'code' => $product_units_new[$i]->code,
                'unit_id' => $product_units_new[$i]->unit_id,
                'conversion_value' => $product_units_new[$i]->conv_value,
                'is_base' => $product_units_new[$i]->is_base,
                'is_primary_unit' => $product_units_new[$i]->is_primary_unit,
                'remarks' => $product_units_new[$i]->remarks
            ]);
        }
    }

    public function test_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $product_group_id = ProductGroup::inRandomOrder()->get()[0]->id;
        $brand_id = Brand::inRandomOrder()->get()[0]->id;
        $name = $this->faker->name;
        $tax_status = (new RandomGenerator())->generateNumber(1,9999);
        $supplier_id = Supplier::inRandomOrder()->get()[0]->id; 
        $remarks = null;
        $point = (new RandomGenerator())->generateNumber(1,9999);
        $use_serial_number = (new RandomGenerator())->generateNumber(0, 1);
        $has_expiry_date = (new RandomGenerator())->generateNumber(0, 1);
        $product_type = (new RandomGenerator())->generateNumber(1, 4);
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $product_units = [];

        $response = $this->service->create(
            $company_id,
            $code,
            $product_group_id,
            $brand_id,
            $name,
            $tax_status,
            $supplier_id,
            $remarks,
            $point,
            $use_serial_number,
            $has_expiry_date,
            $product_type,
            $status,
            $product_units
        );
        $rId = Hashids::decode($response)[0];

        $response = $this->service->delete($rId);
        $deleted_at = Product::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('products', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
}