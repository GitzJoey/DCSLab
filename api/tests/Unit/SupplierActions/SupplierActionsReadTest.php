<?php

namespace Tests\Unit;

use App\Actions\Supplier\SupplierActions;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Tests\ActionsTestCase;

class SupplierActionsReadTest extends ActionsTestCase
{
    private SupplierActions $supplierActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplierActions = new SupplierActions();
    }

    public function test_supplier_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Supplier::factory())
            )->create();

        $supplier = $user->companies()->inRandomOrder()->first()
            ->suppliers()->inRandomOrder()->first();

        $result = $this->supplierActions->read($supplier);

        $this->assertInstanceOf(Supplier::class, $result);
    }
}
