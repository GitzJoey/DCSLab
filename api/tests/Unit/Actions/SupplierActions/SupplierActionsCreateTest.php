<?php

namespace Tests\Unit\Actions\SupplierActions;

use App\Actions\Supplier\SupplierActions;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Tests\ActionsTestCase;

class SupplierActionsCreateTest extends ActionsTestCase
{
    private SupplierActions $supplierActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplierActions = new SupplierActions();
    }

    public function test_supplier_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $payload = Supplier::factory()->for($company)->for($user)
            ->make()->toArray();

        $dto = new \App\DTOs\SupplierCreateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            city: $payload['city'],
            paymentTermType: $payload['payment_term_type'],
            paymentTerm: $payload['payment_term'],
            taxableEnterprise: $payload['taxable_enterprise'],
            taxId: $payload['tax_id'],
            remarks: $payload['remarks'],
            status: $payload['status']
        );

        $result = $this->supplierActions->create($dto);
        $this->assertDatabaseHas('suppliers', [
            'id' => $result->id,
            'user_id' => $payload['user_id'],
            'company_id' => $payload['company_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
            'address' => $payload['address'],
            'city' => $payload['city'],
            'payment_term_type' => $payload['payment_term_type'],
            'payment_term' => $payload['payment_term'],
            'taxable_enterprise' => $payload['taxable_enterprise'],
            'tax_id' => $payload['tax_id'],
            'status' => $payload['status'],
            'remarks' => $payload['remarks'],
        ]);
    }

    public function test_supplier_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $dto = new \App\DTOs\SupplierCreateDTO();

        $this->supplierActions->create($dto);
    }
}
