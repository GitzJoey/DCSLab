<?php

namespace Tests\Feature\API\BrandAPI;

use App\Enums\UserRolesEnum;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class BrandAPICreateTest extends APITestCase
{
    public function test_brand_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $payload = Brand::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.brand.save'), $payload);

        $api->assertUnauthorized();
    }

    public function test_brand_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $payload = Brand::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.brand.save'), $payload);

        $api->assertForbidden();
    }

    public function test_brand_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $payload = Brand::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.brand.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('brands', [
            'company_id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_brand_api_call_store_with_auto_code_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $payload = Brand::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => Config::get('dcslab.KEYWORDS.AUTO'),
        ])->toArray();

        $api = $this->json('POST', route('api.post.brand.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('brands', [
            'company_id' => $company->id,
            'name' => $payload['name'],
        ]);

        $this->assertDatabaseMissing('brands', [
            'company_id' => $company->id,
            'code' => Config::get('dcslab.KEYWORDS.AUTO'),
        ]);
    }

    public function test_brand_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        Brand::factory()->for($company)->create([
            'code' => 'test1',
        ]);

        $payload = Brand::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.brand.save'), $payload);

        $api->assertUnprocessable();
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_brand_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->take(2)->get();

        $company_1 = $companies[0];
        $company_2 = $companies[1];

        Brand::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $payload = Brand::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.brand.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('brands', [
            'company_id' => $company_2->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_brand_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $payload = [];

        $api = $this->json('POST', route('api.post.brand.save'), $payload);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }

    public function test_brand_api_call_store_with_sql_injection_payload_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $payload = Brand::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => "'; DROP TABLE brands; --",
            'name' => "'; DROP TABLE brands; --",
        ])->toArray();

        $api = $this->json('POST', route('api.post.brand.save'), $payload);

        $api->assertSuccessful();

        $this->assertDatabaseHas('brands', [
            'company_id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }
}
