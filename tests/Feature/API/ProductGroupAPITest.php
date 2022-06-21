<?php

namespace Tests\Feature\API;

use Tests\APITestCase;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Actions\RandomGenerator;
use Illuminate\Support\Facades\DB;
use App\Enums\ProductGroupCategory;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductGroupAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        Parent::setUp();
    }
    
    public function test_api_call_require_authentication()
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

    public function test_api_call_save_all()
    {
        $this->actingAs($this->developer);

        $companyId = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $category = $this->faker->randomElement(ProductGroupCategory::toArrayName());

        $api = $this->json('POST', route('api.post.db.product.product_group.save'), [
            'company_id' => $companyId,
            'code' => $code, 
            'name' => $name,
            'category' => $category
        ]);

        $api->assertSuccessful();

        $this->assertDatabaseHas('product_groups', [
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'category' => $category
        ]);
    }

    // public function test_api_call_save_with_existing_code_in_same_company()
    // {
    //     $this->actingAs($this->developer);

    //     $companyId = $this->developer->companies()->whereHas('product_groups')->inRandomOrder()->first()->id;
        
    //     $product_group = ProductGroup::whereCompanyId($companyId)->inRandomOrder()->first();

    //     $code = (new RandomGenerator())->generateAlphaNumeric(5);
    //     $name = $this->faker->name;
    //     $category = $this->faker->boolean();

    //     ProductGroup::create([
    //         'company_id' => $companyId,
    //         'code' => $code, 
    //         'name' => $name,
    //         'category' => $category
    //     ]);

    //     $other_product_group = ProductGroup::whereCompanyId($companyId)->where('id', '!=', $product_group->id)->first();
    //     if (!$other_product_group)
    //         $this->markTestSkipped('There\'s no other product_groups');

    //     $code = $other_product_group->code;
    //     $name = $this->faker->name;
    //     $category = $this->faker->boolean();

    //     $api = $this->json('POST', route('api.post.db.product.product_group.save'), [
    //         'company_id' => Hashids::encode($companyId),
    //         'code' => $code, 
    //         'name' => $name,
    //         'category' => $category
    //     ]);

    //     $api->assertStatus(422);
    //     $api->assertJsonStructure([
    //         'errors'
    //     ]);
    // }

    // public function test_api_call_save_with_existing_code_in_different_company()
    // {
    //     $this->actingAs($this->developer);

    //     $company_1 = $this->developer->companies[0];
    //     $company_2 = $this->developer->companies[1];

    //     $code = (new RandomGenerator())->generateAlphaNumeric(5);

    //     ProductGroup::create([
    //         'company_id' => $company_1->id,
    //         'code' => $code, 
    //         'name' => $this->faker->name,
    //         'category' => 1
    //     ]);

    //     $api = $this->json('POST', route('api.post.db.product.product_group.save'), [
    //         'company_id' => Hashids::encode($company_2->id),
    //         'code' => $code, 
    //         'name' => $this->faker->name,
    //         'category' => $this->faker->randomElement(ProductGroupCategory::toArrayName())
    //     ]);

    //     $api->assertSuccessful();
    //     $this->assertDatabaseHas('product_groups', [
    //         'company_id' => $company_2->id,
    //         'code' => $code
    //     ]);
    // }

    // public function test_api_call_save_with_empty_string_param()
    // {
    //     $companyId = '';
    //     $code = '';
    //     $name = '';
    //     $name = '';
    //     $category = '';

    //     $api = $this->json('POST', route('api.post.db.product.product_group.save'), [
    //         'company_id' => $companyId,
    //         'code' => $code, 
    //         'name' => $name,
    //         'category' => $category
    //     ]);

    //     $api->assertStatus(500);
    //     $api->assertJsonStructure([
    //         'message'
    //     ]);
    // }

    // public function test_api_call_edit_with_all()
    // {
    //     $this->actingAs($this->developer);

    //     $companyId = $this->developer->companies->random(1)->first()->id;
    //     $code = (new RandomGenerator())->generateAlphaNumeric(5);
    //     $name = $this->faker->name;
    //     $category = $this->faker->boolean();

    //     $product_group = ProductGroup::create([
    //         'company_id' => $companyId,
    //         'code' => $code, 
    //         'name' => $name,
    //         'category' => $category
    //     ]);

    //     $product_groupId = $product_group->id;
    //     $newCode = (new RandomGenerator())->generateAlphaNumeric(5) . 'new';
    //     $newName = $this->faker->name;
    //     $NewCategory = $this->faker->boolean();

    //     $api_edit = $this->json('POST', route('api.post.db.product.product_group.edit', [ 'id' => Hashids::encode($product_groupId) ]), [
    //         'company_id' => Hashids::encode($companyId),
    //         'code' => $newCode,
    //         'name' => $newName,
    //         'category' => $NewCategory
    //     ]);

    //     $api_edit->assertSuccessful();
    //     $this->assertDatabaseHas('product_groups', [
    //         'id' => $product_groupId,
    //         'company_id' => $companyId,
    //         'code' => $newCode, 
    //         'name' => $newName,
    //         'category' => $NewCategory
    //     ]);
    // }

    // public function test_api_call_edit_and_change_the_code_with_existing_code_in_the_same_company()
    // {
    //     $this->actingAs($this->developer);

    //     $company = $this->developer->companies()->whereHas('product_groups')->inRandomOrder()->first();
    //     if (!$company)
    //         $this->markTestSkipped('No suitable company found');

    //     $companyId = $company->id;
    //     $code = (new RandomGenerator())->generateAlphaNumeric(5);
    //     $name = $this->faker->name;
    //     $category = $this->faker->boolean();

    //     ProductGroup::create([
    //         'company_id' => $companyId,
    //         'code' => $code, 
    //         'name' => $name,
    //         'category' => $category
    //     ]);

    //     $twoProductGroupes = ProductGroup::whereCompanyId($company->id)->inRandomOrder()->take(2)->get();
    //     if (count($twoProductGroupes) != 2) {
    //         $this->markTestSkipped('Not enough ProductGroup for testing');  
    //     }

    //     $companyId = Hashids::encode($company->id);
    //     $code = $twoProductGroupes[1]->code;
    //     $name = $this->faker->name;
    //     $category = $this->faker->boolean();

    //     $api_edit = $this->json('POST', route('api.post.db.product.product_group.edit', [ 'id' => Hashids::encode($twoProductGroupes[0]->id) ]), [
    //         'company_id' => $companyId,
    //         'code' => $code,
    //         'name' => $name,
    //         'category' => $category
    //     ]);

    //     $api_edit->assertStatus(422);
    //     $api_edit->assertJsonStructure([
    //         'errors',
    //     ]);
    // }

    // public function test_api_call_delete_nonexistance_id()
    // {
    //     $this->actingAs($this->developer);

    //     $api = $this->json('POST', route('api.post.db.product.product_group.delete', (new RandomGenerator())->generateAlphaNumeric(5)));

    //     $api->assertStatus(500);
    // }

    // public function test_api_call_read_with_empty_search()
    // {
    //     $this->actingAs($this->developer);

    //     $company = $this->developer->companies->random(1)->first();
                
    //     $companyId = $company->id;
    //     $search = '';
    //     $paginate = 1;
    //     $page = 1;
    //     $perPage = 10;
    //     $refresh = '';

    //     $api = $this->getJson(route('api.get.db.product.product_group.read', [
    //         'companyId' => Hashids::encode($companyId),
    //         'search' => $search,
    //         'paginate' => $paginate,
    //         'page' => $page,
    //         'perPage' => $perPage,
    //         'refresh' => $refresh
    //     ]));

    //     $api->assertSuccessful();
    //     $api->assertJsonStructure([
    //         'data', 
    //         'links' => [
    //             'first', 'last', 'prev', 'next'
    //         ], 
    //         'meta'=> [
    //             'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
    //         ]
    //     ]);
    // }

    // public function test_api_call_read_with_special_char_in_search()
    // {
    //     $this->actingAs($this->developer);

    //     $company = $this->developer->companies->random(1)->first();

    //     $companyId = $company->id;
    //     $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
    //     $paginate = 1;
    //     $page = 1;
    //     $perPage = 10;
    //     $refresh = '';

    //     $api = $this->getJson(route('api.get.db.product.product_group.read', [
    //         'companyId' => Hashids::encode($companyId),
    //         'search' => $search,
    //         'paginate' => $paginate,
    //         'page' => $page,
    //         'perPage' => $perPage,
    //         'refresh' => $refresh
    //     ]));

    //     $api->assertSuccessful();
    //     $api->assertJsonStructure([
    //         'data', 
    //         'links' => [
    //             'first', 'last', 'prev', 'next'
    //         ], 
    //         'meta'=> [
    //             'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
    //         ]
    //     ]);
    // }

    // public function test_api_call_read_with_negative_value_in_perpage_param()
    // {
    //     $this->actingAs($this->developer);

    //     $company = $this->developer->companies->random(1)->first();

    //     $companyId = $company->id;
    //     $search = "";
    //     $paginate = 1;
    //     $page = 1;
    //     $perPage = -10;
    //     $refresh = '';

    //     $api = $this->getJson(route('api.get.db.product.product_group.read', [
    //         'companyId' => Hashids::encode($companyId),
    //         'search' => $search,
    //         'paginate' => $paginate,
    //         'page' => $page,
    //         'perPage' => $perPage,
    //         'refresh' => $refresh
    //     ]));

    //     $api->assertSuccessful();
    //     $api->assertJsonStructure([
    //         'data', 
    //         'links' => [
    //             'first', 'last', 'prev', 'next'
    //         ], 
    //         'meta'=> [
    //             'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
    //         ]
    //     ]);
    // }

    // public function test_api_call_read_without_pagination()
    // {
    //     $this->actingAs($this->developer);

    //     $company = $this->developer->companies->random(1)->first();

    //     $companyId = $company->id;
    //     $search = "";
    //     $page = 1;
    //     $perPage = 10;
    //     $refresh = '';

    //     $api = $this->getJson(route('api.get.db.product.product_group.read', [
    //         'companyId' => Hashids::encode($companyId),
    //         'search' => $search,
    //         'page' => $page,
    //         'perPage' => $perPage,
    //         'refresh' => $refresh
    //     ]));

    //     $api->assertSuccessful();
    //     $api->assertJsonStructure([
    //         'data', 
    //         'links' => [
    //             'first', 'last', 'prev', 'next'
    //         ], 
    //         'meta'=> [
    //             'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
    //         ]
    //     ]);
    // }
}