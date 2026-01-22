<?php

namespace Tests\Unit\Actions\RepToPascalThisActions;

use App\Actions\RepToPascalThis\RepToPascalThisActions;
use App\Models\Company;
use App\Models\RepToPascalThis;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class RepToPascalThisActionsCreateTest extends ActionsTestCase
{
    private RepToPascalThisActions $RepToCamelThisActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->RepToCamelThisActions = new RepToPascalThisActions();
    }

    public function test_RepToSnakeThis_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $RepToCamelThisArr = RepToPascalThis::factory()->for($company)
            ->make()->toArray();

        $result = $this->RepToCamelThisActions->create($RepToCamelThisArr);

        $this->assertDatabaseHas('RepToSnakePluralsThis', [
            'id' => $result->id,
            'company_id' => $RepToCamelThisArr['company_id'],
            'code' => $RepToCamelThisArr['code'],
            'name' => $RepToCamelThisArr['name'],
        ]);
    }

    public function test_RepToSnakeThis_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->RepToCamelThisActions->create([]);
    }
}
