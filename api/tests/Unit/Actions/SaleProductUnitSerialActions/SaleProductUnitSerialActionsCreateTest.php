<?php

namespace Tests\Unit\Actions\SaleProductUnitSerialActions;

use App\Actions\SaleProductUnitSerial\SaleProductUnitSerialActions;
use App\Models\Company;
use App\Models\SaleProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleProductUnitSerialActionsCreateTest extends ActionsTestCase
{
    private SaleProductUnitSerialActions $saleProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleProductUnitSerialActions = new SaleProductUnitSerialActions();
    }

    public function test_sale_product_unit_serial_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleProductUnitSerialArr = SaleProductUnitSerial::factory()->for($company)
            ->make()->toArray();

        $result = $this->saleProductUnitSerialActions->create($saleProductUnitSerialArr);

        $this->assertDatabaseHas('sale_product_unit_serials', [
            'id' => $result->id,
            'company_id' => $saleProductUnitSerialArr['company_id'],
            'code' => $saleProductUnitSerialArr['code'],
            'name' => $saleProductUnitSerialArr['name'],
        ]);
    }

    public function test_sale_product_unit_serial_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleProductUnitSerialActions->create([]);
    }
}
