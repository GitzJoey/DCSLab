<?php

namespace Tests\Feature\API;

use App\Models\Unit;
use Tests\APITestCase;
use App\Actions\RandomGenerator;
use App\Enums\ProductCategory;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;

class UnitAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        Parent::setUp();
    }
    
    public function test_unit_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/product/unit/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/product/unit/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/product/unit/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/product/unit/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_unit_api_call_unit_save_with_all_field_filled()
    {
        $this->actingAs($this->developer);
        
        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $description = $this->faker->sentence;
        $category = $this->faker->randomElement(ProductCategory::toArrayName());

        $api = $this->json('POST', route('api.post.db.product.unit.save'), [
            'company_id' => Hashids::encode($companyId),
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'category' => $category
        ]);

        $this->assertDatabaseHas('units', [
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'category' => ProductCategory::fromName($category)->value
        ]);
        
        $api->assertSuccessful();
    }

    public function test_unit_api_call_unit_read_with_empty_search()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $category = (new RandomGenerator())->generateNumber(1, 3);
        $search = "";
        $paginate = 1;
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.product.unit.read', [
            'companyId' => Hashids::encode($companyId),
            'category' => $category,
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage
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

    public function test_unit_api_call_unit_edit_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $description = $this->faker->sentence;
        $category = $this->faker->numberBetween(1, 3);

        $unit = Unit::create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'category' => $category
        ]);

        $unitId = $unit->id;
        $newCode = (new RandomGenerator())->generateAlphaNumeric(5) . 'new';
        $newName = $this->faker->name;
        $newDescription = $this->faker->sentence;
        $newCategory = $this->faker->randomElement(ProductCategory::toArrayName());

        $api_edit = $this->json('POST', route('api.post.db.product.unit.edit', [ 'id' => Hashids::encode($unitId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName,
            'description' => $newDescription,
            'category' => $newCategory
        ]);

        $anu = ProductCategory::fromName($newCategory)->value;

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('units', [
            'company_id' => $companyId,
            'code' => $newCode,
            'name' => $newName,
            'description' => $newDescription,
            'category' => ProductCategory::fromName($newCategory)->value
        ]);
    }
    
    public function test_unit_api_call_unit_delete()
    {
        $this->actingAs($this->developer);

        $companyIds = $this->developer->companies->pluck('id');
        $unitId = Unit::whereIn('company_id', $companyIds)->inRandomOrder()->first()->id;
        $hId = Hashids::encode($unitId);

        $api = $this->json('POST', route('api.post.db.product.unit.delete', $hId));

        $api->assertSuccessful();
        $this->assertSoftDeleted('units', [
            'id' => $unitId
        ]);
    }
}