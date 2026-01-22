<?php

namespace Tests\Unit\Actions\SaleProductUnitSerialActions;

use App\Actions\SaleProductUnitSerial\SaleProductUnitSerialActions;
use App\Models\Company;
use App\Models\SaleProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleProductUnitSerialActionsEditTest extends ActionsTestCase
{
    private SaleProductUnitSerialActions $saleProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleProductUnitSerialActions = new SaleProductUnitSerialActions();
    }

    public function test_sale_product_unit_serial_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnitSerial::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleProductUnitSerial = $company->saleProductUnitSerials()->inRandomOrder()->first();

        $saleProductUnitSerialArr = SaleProductUnitSerial::factory()->make()->toArray();

        $result = $this->saleProductUnitSerialActions->update($saleProductUnitSerial, $saleProductUnitSerialArr);

        $this->assertInstanceOf(SaleProductUnitSerial::class, $result);
        $this->assertDatabaseHas('sale_product_unit_serials', [
            'id' => $saleProductUnitSerial->id,
            'company_id' => $saleProductUnitSerial->company_id,
            'code' => $saleProductUnitSerialArr['code'],
            'name' => $saleProductUnitSerialArr['name'],
        ]);
    }

    public function test_sale_product_unit_serial_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnitSerial::factory())
            )->create();

        $saleProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->saleProductUnitSerials()->inRandomOrder()->first();

        $saleProductUnitSerialArr = [];

        $this->saleProductUnitSerialActions->update($saleProductUnitSerial, $saleProductUnitSerialArr);
    }
}
