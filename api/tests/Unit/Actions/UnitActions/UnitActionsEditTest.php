<?php

namespace Tests\Unit\Actions\UnitActions;

use App\Actions\Unit\UnitActions;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class UnitActionsEditTest extends ActionsTestCase
{
    private UnitActions $unitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitActions = new UnitActions();
    }

    public function test_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Unit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $unit = $company->units()->inRandomOrder()->first();

        $unitArr = Unit::factory()->make()->toArray();

        $result = $this->unitActions->update($unit, $unitArr);

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'company_id' => $unit->company_id,
            'code' => $unitArr['code'],
            'name' => $unitArr['name'],
            'description' => $unitArr['description'],
            'type' => $unitArr['type'],
        ]);
    }

    public function test_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Unit::factory())
            )->create();

        $unit = $user->companies()->inRandomOrder()->first()
            ->units()->inRandomOrder()->first();

        $unitArr = [];

        $this->unitActions->update($unit, $unitArr);
    }
}
