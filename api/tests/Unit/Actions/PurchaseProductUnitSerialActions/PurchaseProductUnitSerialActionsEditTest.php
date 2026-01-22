<?php

namespace Tests\Unit\Actions\PurchaseProductUnitSerialActions;

use App\Actions\PurchaseProductUnitSerial\PurchaseProductUnitSerialActions;
use App\Models\Company;
use App\Models\PurchaseProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseProductUnitSerialActionsEditTest extends ActionsTestCase
{
    private PurchaseProductUnitSerialActions $purchaseProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseProductUnitSerialActions = new PurchaseProductUnitSerialActions();
    }

    public function test_purchase_product_unit_serial_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnitSerial::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseProductUnitSerial = $company->purchaseProductUnitSerials()->inRandomOrder()->first();

        $purchaseProductUnitSerialArr = PurchaseProductUnitSerial::factory()->make()->toArray();

        $result = $this->purchaseProductUnitSerialActions->update($purchaseProductUnitSerial, $purchaseProductUnitSerialArr);

        $this->assertInstanceOf(PurchaseProductUnitSerial::class, $result);
        $this->assertDatabaseHas('purchase_product_unit_serials', [
            'id' => $purchaseProductUnitSerial->id,
            'company_id' => $purchaseProductUnitSerial->company_id,
            'code' => $purchaseProductUnitSerialArr['code'],
            'name' => $purchaseProductUnitSerialArr['name'],
        ]);
    }

    public function test_purchase_product_unit_serial_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnitSerial::factory())
            )->create();

        $purchaseProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->purchaseProductUnitSerials()->inRandomOrder()->first();

        $purchaseProductUnitSerialArr = [];

        $this->purchaseProductUnitSerialActions->update($purchaseProductUnitSerial, $purchaseProductUnitSerialArr);
    }
}
