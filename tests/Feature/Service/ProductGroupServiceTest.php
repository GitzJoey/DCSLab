<?php

namespace Tests\Feature\Service;

use App\Models\ProductGroup;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\ProductGroupService;
use App\Actions\RandomGenerator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class ProductGroupServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ProductGroupService::class);
    }

    public function test_product_group_service_call_save()
    {
        $company_id = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $category = (new RandomGenerator())->generateNumber(1, 3);

        $this->service->create(
            company_id: $company_id,
            code: $code,
            name: $name,
            category: $category
        );

        $this->assertDatabaseHas('product_groups', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'category' => $category
        ]);
    }

    public function test_product_group_service_call_read_with_empty_search()
    {
        $companyId = Company::has('productGroups')->inRandomOrder()->first()->id;
        $category = (new RandomGenerator())->generateNumber(1, 3);

        $response = $this->service->read(
            companyId: $companyId,
            category: $category,
            search: '', 
            paginate: true, 
            page: 1,
            perPage: 10,
            useCache: false
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    
    public function test_product_group_service_call_read_with_special_char_in_search()
    {
        $companyId = Company::has('productGroups')->inRandomOrder()->first()->id;
        $category = (new RandomGenerator())->generateNumber(1, 3);
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = true;
        $page = 1;
        $perPage = 10;
        $useCache = false;

        $response = $this->service->read(
            companyId: $companyId,
            category: $category,
            search: $search, 
            paginate: $paginate, 
            page: $page,
            perPage: $perPage,
            useCache: $useCache
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_product_group_service_call_edit()
    {
        $company_id = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $category = (new RandomGenerator())->generateNumber(1, 3);

        $product_group = ProductGroup::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'category' => $category
        ]);
        $id = $product_group->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newCategory = (new RandomGenerator())->generateNumber(1, 3);

        $this->service->update(
            id: $id,
            company_id: $company_id,
            code: $newCode,
            name: $newName,
            category: $newCategory
        );

        $this->assertDatabaseHas('product_groups', [
            'id' => $id,
            'company_id' => $company_id,
            'code' => $newCode,
            'name' => $newName,
            'category' => $newCategory
        ]);
    }

    public function test_product_group_service_call_delete()
    {
        $company_id = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $category = (new RandomGenerator())->generateNumber(1, 3);

        $product_group = ProductGroup::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'category' => $category
        ]);
        $id = $product_group->id;

        $this->service->delete($id);

        $this->assertSoftDeleted('product_groups', [
            'id' => $id
        ]);
    }
}