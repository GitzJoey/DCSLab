<?php

namespace Tests\Feature\API\ProductGroupAPI;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class ProductGroupAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_group_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product_group.save'), $productGroupArr);

        $api->assertStatus(401);
    }

    public function test_product_group_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product_group.save'), $productGroupArr);

        $api->assertStatus(403);
    }

    public function test_product_group_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_product_group_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_product_group_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product_group.save'), $productGroupArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('product_groups', [
            'company_id' => Hashids::decode($productGroupArr['company_id'])[0],
            'code' => $productGroupArr['code'],
            'name' => $productGroupArr['name'],
            'category' => $productGroupArr['category'],
        ]);
    }

    public function test_product_group_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        ProductGroup::factory()->for($company)->create([
            'code' => 'test1',
        ]);

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product_group.save'), $productGroupArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_group_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $productGroupArr = [];
        $api = $this->json('POST', route('api.post.db.product.product_group.save'), $productGroupArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
