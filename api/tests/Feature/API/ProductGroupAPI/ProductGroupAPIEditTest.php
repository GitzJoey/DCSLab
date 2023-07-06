<?php

namespace Tests\Feature\API\ProductGroupAPI;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class ProductGroupAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_group_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()->inRandomOrder()->first();

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product_group.edit', $productGroup->ulid), $productGroupArr);

        $api->assertStatus(401);
    }

    public function test_product_group_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()->inRandomOrder()->first();

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product_group.edit', $productGroup->ulid), $productGroupArr);

        $api->assertStatus(403);
    }

    public function test_product_group_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()->inRandomOrder()->first();

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product_group.edit', $productGroup->ulid), $productGroupArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('product_groups', [
            'id' => $productGroup->id,
            'company_id' => $company->id,
            'code' => $productGroupArr['code'],
            'name' => $productGroupArr['name'],
            'category' => $productGroupArr['category'],
        ]);
    }

    public function test_product_group_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count(2))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroups = $company->productGroups()->inRandomOrder()->take(2)->get();
        $productGroup_1 = $productGroups[0];
        $productGroup_2 = $productGroups[1];

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $productGroup_2->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product_group.edit', $productGroup_1->ulid), $productGroupArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_group_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()))
            ->has(Company::factory()->setStatusActive()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        ProductGroup::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $productGroup_2 = ProductGroup::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product_group.edit', $productGroup_2->ulid), $productGroupArr);

        $api->assertSuccessful();
    }
}
