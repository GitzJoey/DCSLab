<?php

namespace Tests\Feature;

use App\Actions\Brand\BrandActions;
use App\Models\Brand;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrandActionsCreateTest extends TestCase
{
    use WithFaker;
    
    private BrandActions $brandActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandActions = new BrandActions();
    }

    public function test_brand_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                    )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $brandArr = Brand::factory()->for($company)->make()->toArray();

        $result = $this->brandActions->create($brandArr);

        $this->assertDatabaseHas('brands', [
            'id' => $result->id,
            'company_id' => $brandArr['company_id'],
            'code' => $brandArr['code'],
            'name' => $brandArr['name'],
        ]);
    }

    public function test_brand_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->brandActions->create([]);
    }
}
