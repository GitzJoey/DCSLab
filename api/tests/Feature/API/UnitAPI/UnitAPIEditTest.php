<?php

namespace Tests\Feature\API\UnitAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class UnitAPIEditTest extends APITestCase
{
    public function test_unit_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $unit = Unit::factory()->for($company)->create();

        $payload = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.unit.edit', $unit->ulid), $payload);

        $api->assertUnauthorized();
    }

    public function test_unit_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $unit = Unit::factory()->for($company)->create();

        $payload = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.unit.edit', $unit->ulid), $payload);

        $api->assertForbidden();
    }

    public function test_unit_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $unit = Unit::factory()->for($company)->create();

        $payload = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.unit.edit', $unit->ulid), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'company_id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
            'description' => $payload['description'],
            'type' => $payload['type'],
        ]);
    }

    public function test_unit_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        Unit::factory()->for($company)->count(2)->create();

        $units = $company->units()->inRandomOrder()->take(2)->get();
        $unit_1 = $units[0];
        $unit_2 = $units[1];

        $payload = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $unit_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.unit.edit', $unit_2->ulid), $payload);

        $api->assertUnprocessable();
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_unit_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        Unit::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $unit_2 = Unit::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $payload = Unit::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.unit.edit', $unit_2->ulid), $payload);

        $api->assertSuccessful();
    }

    public function test_unit_api_call_update_with_sql_injection_payload_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $unit = Unit::factory()->for($company)->create();

        $payload = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => "'; DROP TABLE units; --",
            'name' => "'; DROP TABLE units; --",
        ])->toArray();

        $api = $this->json('POST', route('api.post.unit.edit', $unit->ulid), $payload);

        $api->assertSuccessful();

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'company_id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
            'description' => $payload['description'],
            'type' => $payload['type'],
        ]);
    }
}
