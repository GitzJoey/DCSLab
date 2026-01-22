<?php

namespace Tests\Unit\Actions\PurchaseReturnProductUnitSerialActions;

use App\Actions\PurchaseReturnProductUnitSerial\PurchaseReturnProductUnitSerialActions;
use App\Models\Company;
use App\Models\PurchaseReturnProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReturnProductUnitSerialActionsCreateTest extends ActionsTestCase
{
    private PurchaseReturnProductUnitSerialActions $purchaseReturnProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnProductUnitSerialActions = new PurchaseReturnProductUnitSerialActions();
    }

    public function test_purchase_return_product_unit_serial_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseReturnProductUnitSerialArr = PurchaseReturnProductUnitSerial::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseReturnProductUnitSerialActions->create($purchaseReturnProductUnitSerialArr);

        $this->assertDatabaseHas('purchase_return_product_unit_serials', [
            'id' => $result->id,
            'company_id' => $purchaseReturnProductUnitSerialArr['company_id'],
            'code' => $purchaseReturnProductUnitSerialArr['code'],
            'name' => $purchaseReturnProductUnitSerialArr['name'],
        ]);
    }

    public function test_purchase_return_product_unit_serial_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseReturnProductUnitSerialActions->create([]);
    }
}
