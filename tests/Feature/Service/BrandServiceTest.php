<?php

namespace Tests\Feature\Service;

use App\Models\Brand;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\BrandService;
use App\Actions\RandomGenerator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class BrandServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BrandService::class);
    }

    public function test_call_save()
    {
        $company_id = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;

        $this->service->create(
            company_id: $company_id,
            code: $code,
            name: $name,
        );

        $this->assertDatabaseHas('brands', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name
        ]);
    }

    public function test_call_read_with_empty_search()
    {
        $companyId = Company::inRandomOrder()->first()->id;

        $response = $this->service->read(
            companyId: $companyId, 
            search: '', 
            paginate: true, 
            page: 1,
            perPage: 10,
            useCache: false
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_read_with_special_char_in_search()
    {
        $companyId = Company::inRandomOrder()->first()->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = true;
        $page = 1;
        $perPage = 10;
        $useCache = false;

        $response = $this->service->read(
            companyId: $companyId, 
            search: $search, 
            paginate: $paginate, 
            page: $page,
            perPage: $perPage,
            useCache: $useCache
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_edit()
    {
        $company_id = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;

        $brand = Brand::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name
        ]);
        $id = $brand->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;

        $this->service->update(
            id: $id,
            company_id: $company_id,
            code: $newCode,
            name: $newName
        );

        $this->assertDatabaseHas('brands', [
            'id' => $id,
            'company_id' => $company_id,
            'code' => $newCode,
            'name' => $newName
        ]);
    }

    public function test_call_delete()
    {
        $company_id = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;

        $brand = Brand::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name
        ]);
        $id = $brand->id;

        $this->service->delete($id);

        $this->assertSoftDeleted('brands', [
            'id' => $id
        ]);
    }
}