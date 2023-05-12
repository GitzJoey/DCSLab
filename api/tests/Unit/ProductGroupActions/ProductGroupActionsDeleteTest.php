<?php

namespace Tests\Unit\ProductGroupActions;

use App\Actions\ProductGroup\ProductGroupActions;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\User;
use Tests\ActionsTestCase;

class ProductGroupActionsDeleteTest extends ActionsTestCase
{
    private ProductGroupActions $productGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupActions = new ProductGroupActions();
    }

    public function test_product_group_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory())
            )->create();

        $productGroup = $user->companies()->inRandomOrder()->first()
            ->productGroups()->inRandomOrder()->first();

        $result = $this->productGroupActions->delete($productGroup);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('product_groups', [
            'id' => $productGroup->id,
        ]);
    }
}
