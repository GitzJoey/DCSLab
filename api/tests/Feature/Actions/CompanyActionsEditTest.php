<?php

namespace Tests\Feature;

use App\Actions\Company\CompanyActions;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyActionsEditTest extends TestCase
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

    public function test_company_service_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyArr = Company::factory()->make();

        $result = $this->companyActions->update($company, $companyArr->toArray());

        $this->assertInstanceOf(Company::class, $result);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
        ]);
    }

    public function test_company_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyArr = [];

        $this->companyActions->update($company, $companyArr);
    }
}
