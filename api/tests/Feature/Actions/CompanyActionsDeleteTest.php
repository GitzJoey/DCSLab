<?php

namespace Tests\Feature;

use App\Actions\Company\CompanyActions;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyActionsDeleteTest extends TestCase
{
    use WithFaker;

    private $companyActions;

    private $companySeeder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = app(CompanyActions::class);

        $this->companySeeder = new CompanyTableSeeder();
    }

    public function test_company_actions_call_delete_expect_bool()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);

        $company = $user->companies->first();

        $result = $this->companyActions->delete($company);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('companies', [
            'id' => $company->id,
        ]);
    }
}
