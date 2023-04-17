<?php

namespace Tests\Feature;

use App\Actions\Brand\BrandActions;
use App\Models\Brand;
use App\Models\User;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrandActionsEditTest extends TestCase
{
    use WithFaker;

    private $brandActions;

    private $companySeeder;

    private $brandSeeder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandActions = app(BrandActions::class);

        $this->companySeeder = new CompanyTableSeeder();
        $this->brandSeeder = new BrandTableSeeder();
    }

    public function test_brand_actions_call_update_expect_db_updated()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $brand = $user->companies->first()->brands->first();
        $brandArr = Brand::factory()->make()->toArray();

        $result = $this->brandActions->update($brand, $brandArr);

        $this->assertInstanceOf(Brand::class, $result);
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'company_id' => $brand->company_id,
            'code' => $brandArr['code'],
            'name' => $brandArr['name'],
        ]);
    }

    public function test_brand_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $brand = $user->companies->first()->brands->first();
        $brandArr = [];

        $this->brandActions->update($brand, $brandArr);
    }
}
