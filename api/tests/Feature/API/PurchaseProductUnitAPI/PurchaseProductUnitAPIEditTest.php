<?php

namespace Tests\Feature\API\PurchaseProductUnitAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\PurchaseProductUnit;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseProductUnitAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_product_unit_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseProductUnit = PurchaseProductUnit::factory()->for($company)->create();

        $purchaseProductUnitArr = PurchaseProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_product_unit.edit', $purchaseProductUnit->ulid), $purchaseProductUnitArr);

        $api->assertStatus(401);
    }

    public function test_purchase_product_unit_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseProductUnit = PurchaseProductUnit::factory()->for($company)->create();

        $purchaseProductUnitArr = PurchaseProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_product_unit.edit', $purchaseProductUnit->ulid), $purchaseProductUnitArr);

        $api->assertStatus(403);
    }

    public function test_purchase_product_unit_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_product_unit_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_product_unit_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseProductUnit = PurchaseProductUnit::factory()->for($company)->create();

        $purchaseProductUnitArr = PurchaseProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_product_unit.edit', $purchaseProductUnit->ulid), $purchaseProductUnitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('purchase_product_units', [
            'id' => $purchaseProductUnit->id,
            'company_id' => $company->id,
            'code' => $purchaseProductUnitArr['code'],
            'name' => $purchaseProductUnitArr['name'],
        ]);
    }

    public function test_purchase_product_unit_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_product_unit_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        PurchaseProductUnit::factory()->for($company)->count(2)->create();

        $purchaseProductUnits = $company->purchaseProductUnits()->inRandomOrder()->take(2)->get();
        $purchaseProductUnit_1 = $purchaseProductUnits[0];
        $purchaseProductUnit_2 = $purchaseProductUnits[1];

        $purchaseProductUnitArr = PurchaseProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $purchaseProductUnit_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_product_unit.edit', $purchaseProductUnit_2->ulid), $purchaseProductUnitArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_purchase_product_unit_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        PurchaseProductUnit::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $purchaseProductUnit_2 = PurchaseProductUnit::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $purchaseProductUnitArr = PurchaseProductUnit::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_product_unit.edit', $purchaseProductUnit_2->ulid), $purchaseProductUnitArr);

        $api->assertSuccessful();
    }
}
