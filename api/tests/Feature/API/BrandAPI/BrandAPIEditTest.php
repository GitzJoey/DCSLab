<?php

namespace Tests\Feature\API\BrandAPI;

use App\Enums\UserRoles;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class BrandAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_brand_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Brand::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $brandArr = Brand::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.brand.edit', $brand->ulid), $brandArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'company_id' => $company->id,
            'code' => $brandArr['code'],
            'name' => $brandArr['name'],
        ]);
    }

    public function test_brand_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Brand::factory()->count(2))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $brands = $company->brands()->inRandomOrder()->take(2)->get();
        $brand_1 = $brands[0];
        $brand_2 = $brands[1];

        $brandArr = Brand::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $brand_2->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.brand.edit', $brand_1->ulid), $brandArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_brand_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory()))
            ->has(
                Company::factory()->setStatusActive()
                    ->has(Brand::factory())
            )->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        Brand::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $brand_2 = Brand::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $brandArr = Brand::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.brand.edit', $brand_2->ulid), $brandArr);

        $api->assertSuccessful();
    }
}
