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

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupActions = new ProductGroupActions();
    }

    public function test_product_group_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count(5))
            )->create();

        $productGroup = $user->companies()->inRandomOrder()->first()
                            ->productGroups()->inRandomOrder()->first();

        $result = $this->productGroupActions->read($productGroup);

        $this->assertInstanceOf(ProductGroup::class, $result);
    }
}
