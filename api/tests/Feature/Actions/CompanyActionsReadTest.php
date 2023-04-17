<?php

namespace Tests\Feature;

use App\Actions\Company\CompanyActions;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyActionsReadTest extends TestCase
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

    public function test_company_actions_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first()->inRandomOrder()->first();

        $result = $this->companyActions->read($company);

        $this->assertInstanceOf(Company::class, $result);
    }
}
