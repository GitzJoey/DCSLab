<?php

namespace Tests\Unit\Actions\RepToPascalThisActions;

use App\Actions\RepToPascalThis\RepToPascalThisActions;
use App\Models\Company;
use App\Models\RepToPascalThis;
use App\Models\User;
use Tests\ActionsTestCase;

class RepToPascalThisActionsDeleteTest extends ActionsTestCase
{
    private RepToPascalThisActions $RepToCamelThisActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->RepToCamelThisActions = new RepToPascalThisActions();
    }

    public function test_RepToSnakeThis_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(RepToPascalThis::factory())
            )->create();

        $RepToCamelThis = $user->companies()->inRandomOrder()->first()
            ->RepToCamelPluralsThis()->inRandomOrder()->first();
        $result = $this->RepToCamelThisActions->delete($RepToCamelThis);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('RepToSnakePluralsThis', [
            'id' => $RepToCamelThis->id,
        ]);
    }
}
