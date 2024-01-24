<?php

namespace Tests\Unit\Actions\BrandActions;

use App\Actions\Brand\BrandActions;
use App\Models\Brand;
use App\Models\Company;
use App\Models\User;
use Tests\ActionsTestCase;

class BrandActionsDeleteTest extends ActionsTestCase
{
    private BrandActions $brandActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandActions = new BrandActions();
    }

    public function test_brand_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory())
            )->create();

        $brand = $user->companies()->inRandomOrder()->first()
            ->brands()->inRandomOrder()->first();

        $result = $this->brandActions->delete($brand);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('brands', [
            'id' => $brand->id,
        ]);
    }
}
