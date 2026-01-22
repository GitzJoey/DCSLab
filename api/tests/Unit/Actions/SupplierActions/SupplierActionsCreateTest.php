<?php

namespace Tests\Unit\Actions\SupplierActions;

use App\Actions\Supplier\SupplierActions;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Exception;
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

        $supplierArr = Supplier::factory()->for($company)->for($user)
            ->make()->toArray();

        $result = $this->supplierActions->create($supplierArr);

        $this->assertDatabaseHas('suppliers', [
            'id' => $result->id,
            'user_id' => $supplierArr['user_id'],
            'company_id' => $supplierArr['company_id'],
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

    public function test_supplier_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->supplierActions->create([]);
    }
}
