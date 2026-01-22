<?php

namespace Tests\Feature\API\SupplierAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SupplierAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_supplier_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $supplier = Supplier::factory()->for($company)->create();

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier->ulid), $supplierArr);

        $api->assertStatus(401);
    }

    public function test_supplier_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $supplier = Supplier::factory()->for($company)->create();

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier->ulid), $supplierArr);

        $api->assertStatus(403);
    }

    public function test_supplier_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_supplier_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_supplier_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $supplier = Supplier::factory()->for($company)->create();

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier->ulid), $supplierArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'company_id' => $company->id,
            'code' => $supplierArr['code'],
            'name' => $supplierArr['name'],
            'address' => $supplierArr['address'],
            'city' => $supplierArr['city'],
            'payment_term_type' => $supplierArr['payment_term_type'],
            'payment_term' => $supplierArr['payment_term'],
            'taxable_enterprise' => $supplierArr['taxable_enterprise'],
            'tax_id' => $supplierArr['tax_id'],
            'status' => $supplierArr['status'],
            'remarks' => $supplierArr['remarks'],
        ]);
    }

    public function test_supplier_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_supplier_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        Supplier::factory()->for($company)->count(2)->create();

        $suppliers = $company->suppliers()->inRandomOrder()->take(2)->get();
        $supplier_1 = $suppliers[0];
        $supplier_2 = $suppliers[1];

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $supplier_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier_2->ulid), $supplierArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_supplier_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        Supplier::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $supplier_2 = Supplier::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier_2->ulid), $supplierArr);

        $api->assertSuccessful();
    }
}
