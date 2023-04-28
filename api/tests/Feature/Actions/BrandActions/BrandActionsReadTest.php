<?php

namespace Tests\Feature;

use App\Actions\Brand\BrandActions;
use App\Models\Brand;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrandActionsReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandActions = new BrandActions();
    }

    public function test_brand_actions_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Brand::factory()->count(5))
                    )->create();

        $brand = $user->companies()->inRandomOrder()->first()
                    ->brands()->inRandomOrder()->first();

        $result = $this->brandActions->read($brand);

        $this->assertInstanceOf(Brand::class, $result);
    }
}
