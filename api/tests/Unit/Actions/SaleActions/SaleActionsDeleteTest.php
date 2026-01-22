<?php

namespace Tests\Unit\Actions\SaleActions;

use App\Actions\Sale\SaleActions;
use App\Models\Company;
use App\Models\Sale;
use App\Models\User;
use Tests\ActionsTestCase;

class SaleActionsDeleteTest extends ActionsTestCase
{
    private SaleActions $saleActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleActions = new SaleActions();
    }

    public function test_sale_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Sale::factory())
            )->create();

        $sale = $user->companies()->inRandomOrder()->first()
            ->sales()->inRandomOrder()->first();
        $result = $this->saleActions->delete($sale);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sales', [
            'id' => $sale->id,
        ]);
    }
}
