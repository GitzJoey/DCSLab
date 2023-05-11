<?php

namespace Tests\Unit;

use App\Actions\Supplier\SupplierActions;
use App\Models\Company;
use App\Models\Profile;
use App\Models\Supplier;
use App\Models\User;

use Tests\ActionsTestCase;

class SupplierActionsDeleteTest extends ActionsTestCase
{
    private SupplierActions $supplierActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplierActions = new SupplierActions();
    }

    public function test_supplier_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Profile::factory()->setStatusActive())
            ->has(Company::factory()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        Supplier::factory()->for($user)->for($company)->setStatusActive()->create();

        $supplier = $user->companies()->inRandomOrder()->first()
            ->suppliers()->inRandomOrder()->first();

        $result = $this->supplierActions->delete($supplier);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);
    }
}
