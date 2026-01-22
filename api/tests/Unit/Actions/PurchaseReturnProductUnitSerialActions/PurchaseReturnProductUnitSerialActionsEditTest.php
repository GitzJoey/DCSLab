<?php

namespace Tests\Unit\Actions\PurchaseReturnProductUnitSerialActions;

use App\Actions\PurchaseReturnProductUnitSerial\PurchaseReturnProductUnitSerialActions;
use App\Models\Company;
use App\Models\PurchaseReturnProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReturnProductUnitSerialActionsEditTest extends ActionsTestCase
{
    private PurchaseReturnProductUnitSerialActions $purchaseReturnProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnProductUnitSerialActions = new PurchaseReturnProductUnitSerialActions();
    }

    public function test_purchase_return_product_unit_serial_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnProductUnitSerial::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnProductUnitSerial = $company->purchaseReturnProductUnitSerials()->inRandomOrder()->first();

        $purchaseReturnProductUnitSerialArr = PurchaseReturnProductUnitSerial::factory()->make()->toArray();

        $result = $this->purchaseReturnProductUnitSerialActions->update($purchaseReturnProductUnitSerial, $purchaseReturnProductUnitSerialArr);

        $this->assertInstanceOf(PurchaseReturnProductUnitSerial::class, $result);
        $this->assertDatabaseHas('purchase_return_product_unit_serials', [
            'id' => $purchaseReturnProductUnitSerial->id,
            'company_id' => $purchaseReturnProductUnitSerial->company_id,
            'code' => $purchaseReturnProductUnitSerialArr['code'],
            'name' => $purchaseReturnProductUnitSerialArr['name'],
        ]);
    }

    public function test_purchase_return_product_unit_serial_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnProductUnitSerial::factory())
            )->create();

        $purchaseReturnProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->purchaseReturnProductUnitSerials()->inRandomOrder()->first();

        $purchaseReturnProductUnitSerialArr = [];

        $this->purchaseReturnProductUnitSerialActions->update($purchaseReturnProductUnitSerial, $purchaseReturnProductUnitSerialArr);
    }
}
