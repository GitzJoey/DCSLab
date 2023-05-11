<?php

namespace Tests\Feature\API;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class UnitAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_unit_api_call_store_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $unitArr = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.unit.save'), $unitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('units', [
            'company_id' => Hashids::decode($unitArr['company_id'])[0],
            'code' => $unitArr['code'],
            'name' => $unitArr['name'],
            'description' => $unitArr['description'],
            'category' => $unitArr['category'],
        ]);
    }

    public function test_unit_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        Unit::factory()->for($company)->create([
            'code' => 'test1',
        ]);

        $unitArr = Unit::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.unit.save'), $unitArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_unit_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->create();

        $this->actingAs($user);

        $unitArr = [];
        $api = $this->json('POST', route('api.post.db.product.unit.save'), $unitArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
