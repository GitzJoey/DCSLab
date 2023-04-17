<?php

namespace Tests\Feature;

use App\Actions\Company\CompanyActions;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyActionsCreateTest extends TestCase
{
    use WithFaker;

    private $companyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = app(CompanyActions::class);
    }

    public function test_company_action_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyArr = Company::factory()->make([
            'user_id' => $user->id,
        ]);

        $result = $this->companyActions->create($companyArr->toArray());

        $this->assertDatabaseHas('companies', [
            'id' => $result->id,
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
        ]);
    }

    public function test_company_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->companyActions->create([]);
    }
}
