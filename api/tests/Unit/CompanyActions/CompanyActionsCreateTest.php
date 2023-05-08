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
    
    private CompanyActions $companyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = new CompanyActions();
    }

    public function test_company_action_call_create_expect_db_has_record()
    {
        $user = User::factory()->create();

        $companyArr = Company::factory()
                        ->setStatusActive()->setIsDefault()->make([
                            'user_id' => $user->id,
                        ])->toArray();

        $result = $this->companyActions->create($companyArr);

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
