<?php

namespace Tests\Feature;

use App\Actions\ProductGroup\ProductGroupActions;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductGroupActionsReadTest extends TestCase
{
    use WithFaker;

    private $productGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupActions = app(ProductGroupActions::class);
    }

    public function test_product_group_actions_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(ProductGroup::factory()->count(20), 'productGroups'), 'companies')
                    ->create();

        $productGroup = $user->companies->first()->productGroups()->inRandomOrder()->first();

        $result = $this->productGroupActions->read($productGroup);

        $this->assertInstanceOf(ProductGroup::class, $result);
    }
}
