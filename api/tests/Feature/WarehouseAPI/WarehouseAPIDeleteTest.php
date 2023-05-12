<?php

namespace Tests\Feature\WarehouseAPI;

use Exception;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use App\Models\Warehouse;
use Illuminate\Support\Str;

class WarehouseAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_warehouse_api_call_delete_expect_successful()
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

        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', $warehouse->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    public function test_warehouse_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_warehouse_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', null));

        $api->assertStatus(500);
    }
}
