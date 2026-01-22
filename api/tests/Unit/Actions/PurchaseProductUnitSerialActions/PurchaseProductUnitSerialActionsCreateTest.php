<?php

namespace Tests\Unit\Actions\PurchaseProductUnitSerialActions;

use App\Actions\PurchaseProductUnitSerial\PurchaseProductUnitSerialActions;
use App\Models\Company;
use App\Models\PurchaseProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseProductUnitSerialActionsCreateTest extends ActionsTestCase
{
    private PurchaseProductUnitSerialActions $purchaseProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseProductUnitSerialActions = new PurchaseProductUnitSerialActions();
    }

    public function test_purchase_product_unit_serial_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseProductUnitSerialArr = PurchaseProductUnitSerial::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseProductUnitSerialActions->create($purchaseProductUnitSerialArr);

        $this->assertDatabaseHas('purchase_product_unit_serials', [
            'id' => $result->id,
            'company_id' => $purchaseProductUnitSerialArr['company_id'],
            'code' => $purchaseProductUnitSerialArr['code'],
            'name' => $purchaseProductUnitSerialArr['name'],
        ]);
    }

    public function test_purchase_product_unit_serial_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseProductUnitSerialActions->create([]);
    }
}
