<?php

namespace Tests\Feature\API;

use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WarehouseAPIReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_warehouse_api_call_read_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $ulid = $warehouse->ulid;

        $api = $this->getJson(route('api.get.db.company.warehouse.read', $ulid));

        $api->assertSuccessful();
    }

    public function test_warehouse_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    )->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.company.warehouse.read', null));
    }

    public function test_warehouse_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    )->create();

        $this->actingAs($user);

        $ulid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.company.warehouse.read', $ulid));

        $api->assertStatus(404);
    }
}
