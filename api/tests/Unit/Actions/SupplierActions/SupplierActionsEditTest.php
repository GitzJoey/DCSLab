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

        $supplierArr = Supplier::factory()->make()->toArray();

        $result = $this->supplierActions->update($supplier, $supplierArr);

        $this->assertInstanceOf(Supplier::class, $result);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'company_id' => $supplier->company_id,
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

    public function test_supplier_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Supplier::factory())
            )->create();

        $supplier = $user->companies()->inRandomOrder()->first()
            ->suppliers()->inRandomOrder()->first();

        $supplierArr = [];

        $this->supplierActions->update($supplier, $supplierArr);
    }
}
