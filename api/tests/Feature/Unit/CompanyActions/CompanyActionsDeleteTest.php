<?php

namespace Tests\Feature;

use App\Actions\Company\CompanyActions;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyActionsDeleteTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = new CompanyActions();
    }

    public function test_company_actions_call_delete_expect_bool()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault())
                ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->companyActions->delete($company);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('companies', [
            'id' => $company->id,
        ]);
    }
}
