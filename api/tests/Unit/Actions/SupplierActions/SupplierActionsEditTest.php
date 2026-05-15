<?php

namespace Tests\Unit\Actions\SupplierActions;

use App\Actions\Supplier\SupplierActions;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SupplierActionsEditTest extends ActionsTestCase
{
    private SupplierActions $supplierActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplierActions = new SupplierActions();
    }

    public function test_supplier_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Supplier::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $supplier = $company->suppliers()->inRandomOrder()->first();

        $payload = Supplier::factory()->make()->toArray();

        $dto = new \App\DTOs\SupplierUpdateDTO(
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

        $result = $this->supplierActions->update($supplier, $dto);
        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'company_id' => $supplier->company_id,
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

    public function test_supplier_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Supplier::factory())
            )->create();

        $supplier = $user->companies()->inRandomOrder()->first()
            ->suppliers()->inRandomOrder()->first();

        $payload = [];

        $dto = new \App\DTOs\SupplierUpdateDTO();

        $this->supplierActions->update($supplier, $dto);
    }
}
