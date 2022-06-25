<?php

namespace Tests\Feature\API;

use Tests\APITestCase;
use App\Models\Company;
use App\Actions\RandomGenerator;
use App\Enums\ProductCategory;
use App\Models\ProductGroup;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;

class ProductGroupAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        Parent::setUp();
    }
    
    public function test_product_group_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/product/product_group/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/product/product_group/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/product/product_group/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/product/product_group/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_product_group_api_call_product_group_save_with_all_field_filled()
    {
        $this->actingAs($this->developer);
        
        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $category = $this->faker->randomElement(ProductCategory::toArrayName());

        $api = $this->json('POST', route('api.post.db.product.product_group.save'), [
            'company_id' => Hashids::encode($companyId),
            'code' => $code,
            'name' => $name,
            'category' => $category
        ]);

        $this->assertDatabaseHas('product_groups', [
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'category' => ProductCategory::fromName($category)->value
        ]);
        
        $api->assertSuccessful();
    }

    public function test_product_group_api_call_read_with_empty_search()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();
                
        $companyId = $company->id;
        $search = '';
        $paginate = 1;
        $page = 1;
        $perPage = 10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.product.product_group.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,
            'refresh' => $refresh
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_product_group_api_call_read_with_special_char_in_search()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = 1;
        $page = 1;
        $perPage = 10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.product.product_group.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,
            'refresh' => $refresh
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_product_group_api_call_read_with_negative_value_in_perpage_param()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = "";
        $paginate = 1;
        $page = 1;
        $perPage = -10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.product.product_group.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,
            'refresh' => $refresh
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_product_group_api_call_read_without_pagination()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = "";
        $page = 1;
        $perPage = 10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.product.product_group.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'refresh' => $refresh
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_product_group_api_call_product_group_edit_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $category = $this->faker->numberBetween(1, 3);

        $productGroup = ProductGroup::create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'category' => $category
        ]);

        $productGroupId = $productGroup->id;
        $newCode = (new RandomGenerator())->generateAlphaNumeric(5) . 'new';
        $newName = $this->faker->name;
        $newCategory = $this->faker->randomElement(ProductCategory::toArrayName());

        $api_edit = $this->json('POST', route('api.post.db.product.product_group.edit', [ 'id' => Hashids::encode($productGroupId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName,
            'category' => $newCategory
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('product_groups', [
            'company_id' => $companyId,
            'code' => $newCode,
            'name' => $newName,
            'category' => ProductCategory::fromName($newCategory)->value
        ]);
    }
    
    public function test_product_group_api_call_product_group_delete()
    {
        $this->actingAs($this->developer);

        $companyIds = $this->developer->companies->pluck('id');
        $productGroupId = ProductGroup::whereIn('company_id', $companyIds)->inRandomOrder()->first()->id;
        $hId = Hashids::encode($productGroupId);

        $api = $this->json('POST', route('api.post.db.product.product_group.delete', $hId));

        $api->assertSuccessful();
        $this->assertSoftDeleted('product_groups', [
            'id' => $productGroupId
        ]);
    }

    public function test_product_group_api_call_delete_nonexistance_id()
    {
        $this->actingAs($this->developer);

        $api = $this->json('POST', route('api.post.db.product.product_group.delete', (new RandomGenerator())->generateAlphaNumeric(5)));

        $api->assertStatus(500);
    }
}