<?php

namespace Tests\Feature\API\PurchaseReturnProductUnitAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\PurchaseReturnProductUnit;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;

class PurchaseReturnProductUnitAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_return_product_unit_api_call_delete_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnProductUnit = PurchaseReturnProductUnit::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_product_unit.delete', $purchaseReturnProductUnit->ulid));

        $api->assertStatus(401);
    }

    public function test_purchase_return_product_unit_api_call_delete_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnProductUnit = PurchaseReturnProductUnit::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_product_unit.delete', $purchaseReturnProductUnit->ulid));

        $api->assertStatus(403);
    }

    public function test_purchase_return_product_unit_api_call_delete_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnProductUnit = PurchaseReturnProductUnit::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_product_unit.delete', $purchaseReturnProductUnit->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('purchase_return_product_units', [
            'id' => $purchaseReturnProductUnit->id,
        ]);
    }

    public function test_purchase_return_product_unit_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_product_unit.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_purchase_return_product_unit_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_product_unit.delete', null));

        $api->assertStatus(500);
    }
}
