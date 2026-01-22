<?php

namespace Tests\Feature\API\SaleProductUnitAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\SaleProductUnit;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SaleProductUnitAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_sale_product_unit_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleProductUnit = SaleProductUnit::factory()->for($company)->create();

        $saleProductUnitArr = SaleProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_product_unit.edit', $saleProductUnit->ulid), $saleProductUnitArr);

        $api->assertStatus(401);
    }

    public function test_sale_product_unit_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $saleProductUnit = SaleProductUnit::factory()->for($company)->create();

        $saleProductUnitArr = SaleProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_product_unit.edit', $saleProductUnit->ulid), $saleProductUnitArr);

        $api->assertStatus(403);
    }

    public function test_sale_product_unit_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_product_unit_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_product_unit_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $saleProductUnit = SaleProductUnit::factory()->for($company)->create();

        $saleProductUnitArr = SaleProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_product_unit.edit', $saleProductUnit->ulid), $saleProductUnitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('sale_product_units', [
            'id' => $saleProductUnit->id,
            'company_id' => $company->id,
            'code' => $saleProductUnitArr['code'],
            'name' => $saleProductUnitArr['name'],
        ]);
    }

    public function test_sale_product_unit_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_product_unit_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        SaleProductUnit::factory()->for($company)->count(2)->create();

        $saleProductUnits = $company->saleProductUnits()->inRandomOrder()->take(2)->get();
        $saleProductUnit_1 = $saleProductUnits[0];
        $saleProductUnit_2 = $saleProductUnits[1];

        $saleProductUnitArr = SaleProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $saleProductUnit_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_product_unit.edit', $saleProductUnit_2->ulid), $saleProductUnitArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_sale_product_unit_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        SaleProductUnit::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $saleProductUnit_2 = SaleProductUnit::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $saleProductUnitArr = SaleProductUnit::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_product_unit.edit', $saleProductUnit_2->ulid), $saleProductUnitArr);

        $api->assertSuccessful();
    }
}
