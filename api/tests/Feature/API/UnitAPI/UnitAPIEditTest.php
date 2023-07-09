<?php

namespace Tests\Feature\API\UnitAPI;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class UnitAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_unit_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Unit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $unit = $company->units()->inRandomOrder()->first();

        $unitArr = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.unit.edit', $unit->ulid), $unitArr);

        $api->assertStatus(401);
    }

    public function test_unit_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Unit::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $unit = $company->units()->inRandomOrder()->first();

        $unitArr = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.unit.edit', $unit->ulid), $unitArr);

        $api->assertStatus(403);
    }

    public function test_unit_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_unit_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_unit_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Unit::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $unit = $company->units()->inRandomOrder()->first();

        $unitArr = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.unit.edit', $unit->ulid), $unitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'company_id' => $company->id,
            'code' => $unitArr['code'],
            'name' => $unitArr['name'],
            'description' => $unitArr['description'],
            'category' => $unitArr['category'],
        ]);
    }

    public function test_unit_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Unit::factory()->count(2))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $units = $company->units()->inRandomOrder()->take(2)->get();
        $unit_1 = $units[0];
        $unit_2 = $units[1];

        $unitArr = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $unit_2->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.unit.edit', $unit_1->ulid), $unitArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_unit_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Unit::factory()))
            ->has(Company::factory()->setStatusActive()
                ->has(Unit::factory())
            )->create();

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

        $unitArr = Unit::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.unit.edit', $unit_2->ulid), $unitArr);

        $api->assertSuccessful();
    }
}
