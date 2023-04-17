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

class BrandActionsCreateTest extends TestCase
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

    public function test_brand_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $brandArr = Brand::factory()->make([
            'company_id' => $companyId,
        ])->toArray();

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
