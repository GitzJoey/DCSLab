<?php

namespace Tests\Unit\Actions\RepToPascalThisActions;

use App\Actions\RepToPascalThis\RepToPascalThisActions;
use App\Models\Company;
use App\Models\RepToPascalThis;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class RepToPascalThisActionsEditTest extends ActionsTestCase
{
    private RepToPascalThisActions $RepToCamelThisActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->RepToCamelThisActions = new RepToPascalThisActions();
    }

    public function test_RepToSnakeThis_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(RepToPascalThis::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $RepToCamelThis = $company->RepToCamelPluralsThis()->inRandomOrder()->first();

        $RepToCamelThisArr = RepToPascalThis::factory()->make()->toArray();

        $result = $this->RepToCamelThisActions->update($RepToCamelThis, $RepToCamelThisArr);

        $this->assertInstanceOf(RepToPascalThis::class, $result);
        $this->assertDatabaseHas('RepToSnakePluralsThis', [
            'id' => $RepToCamelThis->id,
            'company_id' => $RepToCamelThis->company_id,
            'code' => $RepToCamelThisArr['code'],
            'name' => $RepToCamelThisArr['name'],
        ]);
    }

    public function test_RepToSnakeThis_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(RepToPascalThis::factory())
            )->create();

        $RepToCamelThis = $user->companies()->inRandomOrder()->first()
            ->RepToCamelPluralsThis()->inRandomOrder()->first();

        $RepToCamelThisArr = [];

        $this->RepToCamelThisActions->update($RepToCamelThis, $RepToCamelThisArr);
    }
}
