<?php

namespace Tests\Feature\API\NonCapitalAdditionCategoryAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\NonCapitalAdditionCategory;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class NonCapitalAdditionCategoryAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_non_capital_addition_category_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalAdditionCategory = NonCapitalAdditionCategory::factory()->for($company)->create();

        $nonCapitalAdditionCategoryArr = NonCapitalAdditionCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition_category.edit', $nonCapitalAdditionCategory->ulid), $nonCapitalAdditionCategoryArr);

        $api->assertStatus(401);
    }

    public function test_non_capital_addition_category_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalAdditionCategory = NonCapitalAdditionCategory::factory()->for($company)->create();

        $nonCapitalAdditionCategoryArr = NonCapitalAdditionCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition_category.edit', $nonCapitalAdditionCategory->ulid), $nonCapitalAdditionCategoryArr);

        $api->assertStatus(403);
    }

    public function test_non_capital_addition_category_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_non_capital_addition_category_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_non_capital_addition_category_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalAdditionCategory = NonCapitalAdditionCategory::factory()->for($company)->create();

        $nonCapitalAdditionCategoryArr = NonCapitalAdditionCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition_category.edit', $nonCapitalAdditionCategory->ulid), $nonCapitalAdditionCategoryArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('non_capital_addition_categories', [
            'id' => $nonCapitalAdditionCategory->id,
            'company_id' => $company->id,
            'code' => $nonCapitalAdditionCategoryArr['code'],
            'name' => $nonCapitalAdditionCategoryArr['name'],
        ]);
    }

    public function test_non_capital_addition_category_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_non_capital_addition_category_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        NonCapitalAdditionCategory::factory()->for($company)->count(2)->create();

        $nonCapitalAdditionCategories = $company->nonCapitalAdditionCategories()->inRandomOrder()->take(2)->get();
        $nonCapitalAdditionCategory_1 = $nonCapitalAdditionCategories[0];
        $nonCapitalAdditionCategory_2 = $nonCapitalAdditionCategories[1];

        $nonCapitalAdditionCategoryArr = NonCapitalAdditionCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $nonCapitalAdditionCategory_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition_category.edit', $nonCapitalAdditionCategory_2->ulid), $nonCapitalAdditionCategoryArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_non_capital_addition_category_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        NonCapitalAdditionCategory::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $nonCapitalAdditionCategory_2 = NonCapitalAdditionCategory::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $nonCapitalAdditionCategoryArr = NonCapitalAdditionCategory::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition_category.edit', $nonCapitalAdditionCategory_2->ulid), $nonCapitalAdditionCategoryArr);

        $api->assertSuccessful();
    }
}
