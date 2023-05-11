<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use Illuminate\Support\Str;

class UnitAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_unit_api_call_delete_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Unit::factory())
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $unit = $company->units()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.product.unit.delete', $unit->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('units', [
            'id' => $unit->id,
        ]);
    }

    public function test_unit_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.db.product.unit.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_unit_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.product.unit.delete', null));
    }
}
