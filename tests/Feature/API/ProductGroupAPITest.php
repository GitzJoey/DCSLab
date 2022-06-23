<?php

namespace Tests\Feature\API;

use Tests\APITestCase;
use App\Models\Company;
use App\Actions\RandomGenerator;
use App\Enums\ProductGroupCategory;
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

    public function test_api_call_product_group_save_with_all_field_filled()
    {
        $this->actingAs($this->user);
        
        $companyId = $this->user->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $category = $this->faker->randomElement(ProductGroupCategory::toArrayName());

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
            'category' => ProductGroupCategory::fromName($category)->value
        ]);
        
        $api->assertSuccessful();
    }

    public function test_api_call_product_group_edit_with_all_field_filled()
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
        $newCategory = $this->faker->randomElement(ProductGroupCategory::toArrayName());

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
            'category' => ProductGroupCategory::fromName($newCategory)->value
        ]);
    }
    
    public function test_api_call_product_group_delete()
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
}