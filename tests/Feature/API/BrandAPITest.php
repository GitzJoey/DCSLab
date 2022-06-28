<?php

namespace Tests\Feature\API;

use App\Models\Brand;
use Tests\APITestCase;
use App\Actions\RandomGenerator;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;

class BrandAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        Parent::setUp();
    }
    
    public function test_brand_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/product/brand/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/product/brand/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/product/brand/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/product/brand/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_brand_api_call_brand_save_with_all_field_filled()
    {
        $this->actingAs($this->developer);
        
        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;

        $api = $this->json('POST', route('api.post.db.product.brand.save'), [
            'company_id' => Hashids::encode($companyId),
            'code' => $code,
            'name' => $name
        ]);

        $this->assertDatabaseHas('brands', [
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name
        ]);
        
        $api->assertSuccessful();
    }

    public function test_brand_api_call_read_with_empty_search()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();
                
        $companyId = $company->id;
        $search = '';
        $paginate = 1;
        $page = 1;
        $perPage = 10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.product.brand.read', [
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

    public function test_brand_api_call_read_with_special_char_in_search()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = 1;
        $page = 1;
        $perPage = 10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.product.brand.read', [
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

    public function test_brand_api_call_read_with_negative_value_in_perpage_param()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = "";
        $paginate = 1;
        $page = 1;
        $perPage = -10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.product.brand.read', [
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

    public function test_brand_api_call_read_without_pagination()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = "";
        $page = 1;
        $perPage = 10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.product.brand.read', [
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

    public function test_brand_api_call_brand_edit_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;

        $brand = Brand::create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name
        ]);

        $brandId = $brand->id;
        $newCode = (new RandomGenerator())->generateAlphaNumeric(5) . 'new';
        $newName = $this->faker->name;

        $api_edit = $this->json('POST', route('api.post.db.product.brand.edit', [ 'id' => Hashids::encode($brandId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('brands', [
            'company_id' => $companyId,
            'code' => $newCode,
            'name' => $newName
        ]);
    }
    
    public function test_brand_api_call_brand_delete()
    {
        $this->actingAs($this->developer);

        $companyIds = $this->developer->companies->pluck('id');
        $brandId = Brand::whereIn('company_id', $companyIds)->inRandomOrder()->first()->id;
        $hId = Hashids::encode($brandId);

        $api = $this->json('POST', route('api.post.db.product.brand.delete', $hId));

        $api->assertSuccessful();
        $this->assertSoftDeleted('brands', [
            'id' => $brandId
        ]);
    }

    public function test_brand_api_call_delete_nonexistance_id()
    {
        $this->actingAs($this->developer);

        $api = $this->json('POST', route('api.post.db.product.brand.delete', (new RandomGenerator())->generateAlphaNumeric(5)));

        $api->assertStatus(500);
    }
}