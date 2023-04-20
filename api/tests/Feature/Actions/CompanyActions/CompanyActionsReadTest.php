<?php

namespace Tests\Feature;

use App\Actions\Company\CompanyActions;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyActionsReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = new CompanyActions();
    }

    public function test_company_actions_call_read_expect_object()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault())
                ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->companyActions->read($company);

        $this->assertInstanceOf(Company::class, $result);
    }
}
