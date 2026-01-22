<?php

namespace Tests\Feature\API\StockTransferProductUnitAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\StockTransferProductUnit;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class StockTransferProductUnitAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_stock_transfer_product_unit_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $stockTransferProductUnitArr = StockTransferProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_product_unit.save'), $stockTransferProductUnitArr);

        $api->assertUnauthorized();
    }

    public function test_stock_transfer_product_unit_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $stockTransferProductUnitArr = StockTransferProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_product_unit.save'), $stockTransferProductUnitArr);

        $api->assertForbidden();
    }

    public function test_stock_transfer_product_unit_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_stock_transfer_product_unit_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_stock_transfer_product_unit_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $stockTransferProductUnitArr = StockTransferProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_product_unit.save'), $stockTransferProductUnitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_transfer_product_units', [
            'company_id' => $company->id,
            'code' => $stockTransferProductUnitArr['code'],
            'name' => $stockTransferProductUnitArr['name'],
        ]);
    }

    public function test_stock_transfer_product_unit_api_call_store_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_stock_transfer_product_unit_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        StockTransferProductUnit::factory()->for($company)->create([
            'code' => 'test1',
        ]);

        $stockTransferProductUnitArr = StockTransferProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_product_unit.save'), $stockTransferProductUnitArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_stock_transfer_product_unit_api_call_store_with_existing_code_in_different_company_expect_successful()
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

        StockTransferProductUnit::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $stockTransferProductUnitArr = StockTransferProductUnit::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_product_unit.save'), $stockTransferProductUnitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_transfer_product_units', [
            'company_id' => $company_2->id,
            'code' => $stockTransferProductUnitArr['code'],
            'name' => $stockTransferProductUnitArr['name'],
        ]);
    }

    public function test_stock_transfer_product_unit_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $stockTransferProductUnitArr = [];

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_product_unit.save'), $stockTransferProductUnitArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
