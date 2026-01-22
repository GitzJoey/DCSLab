<?php

namespace Tests\Feature\API\SaleReceiptProductUnitAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\SaleReceiptProductUnit;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SaleReceiptProductUnitAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_sale_receipt_product_unit_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleReceiptProductUnitArr = SaleReceiptProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit.save'), $saleReceiptProductUnitArr);

        $api->assertUnauthorized();
    }

    public function test_sale_receipt_product_unit_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $saleReceiptProductUnitArr = SaleReceiptProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit.save'), $saleReceiptProductUnitArr);

        $api->assertForbidden();
    }

    public function test_sale_receipt_product_unit_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_receipt_product_unit_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_sale_receipt_product_unit_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $saleReceiptProductUnitArr = SaleReceiptProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit.save'), $saleReceiptProductUnitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('sale_receipt_product_units', [
            'company_id' => $company->id,
            'code' => $saleReceiptProductUnitArr['code'],
            'name' => $saleReceiptProductUnitArr['name'],
        ]);
    }

    public function test_sale_receipt_product_unit_api_call_store_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_receipt_product_unit_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        SaleReceiptProductUnit::factory()->for($company)->create([
            'code' => 'test1',
        ]);

        $saleReceiptProductUnitArr = SaleReceiptProductUnit::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit.save'), $saleReceiptProductUnitArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_sale_receipt_product_unit_api_call_store_with_existing_code_in_different_company_expect_successful()
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

        SaleReceiptProductUnit::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $saleReceiptProductUnitArr = SaleReceiptProductUnit::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit.save'), $saleReceiptProductUnitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('sale_receipt_product_units', [
            'company_id' => $company_2->id,
            'code' => $saleReceiptProductUnitArr['code'],
            'name' => $saleReceiptProductUnitArr['name'],
        ]);
    }

    public function test_sale_receipt_product_unit_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $saleReceiptProductUnitArr = [];

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit.save'), $saleReceiptProductUnitArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
