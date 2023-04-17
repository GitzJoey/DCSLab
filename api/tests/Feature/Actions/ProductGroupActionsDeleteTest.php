<?php

namespace Tests\Feature;

use App\Actions\ProductGroup\ProductGroupActions;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductGroupActionsDeleteTest extends TestCase
{
    use WithFaker;

    private $productGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupActions = app(ProductGroupActions::class);
    }

    public function test_product_group_actions_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(ProductGroup::factory()->count(5), 'productGroups'), 'companies')
                    ->create();

        $productGroup = $user->companies->first()->productGroups->first();

        $result = $this->productGroupActions->delete($productGroup);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('product_groups', [
            'id' => $productGroup->id,
        ]);
    }
}
