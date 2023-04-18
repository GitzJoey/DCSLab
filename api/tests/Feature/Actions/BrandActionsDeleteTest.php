<?php

namespace Tests\Feature;

use App\Actions\Brand\BrandActions;
use App\Models\User;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrandActionsDeleteTest extends TestCase
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

    public function test_brand_actions_call_delete_expect_bool()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $brand = $user->companies->first()->brands->first();

        $result = $this->brandActions->delete($brand);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('brands', [
            'id' => $brand->id,
        ]);
    }
}
