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

    }

    public function test_call_save()
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

    public function test_call_edit()
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

    public function test_call_delete()
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

    public function test_call_read_when_user_have_product_groups_read_with_empty_search()
    {
        $companyId = Company::has('productGroups')->inRandomOrder()->first()->id;

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

    public function test_call_read_when_user_have_product_groups_with_special_char_in_search()
    {
        $companyId = Company::has('productGroups')->inRandomOrder()->first()->id;
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
}
