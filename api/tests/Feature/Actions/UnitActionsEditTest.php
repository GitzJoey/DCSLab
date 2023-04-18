<?php

namespace Tests\Feature;

use App\Actions\Unit\UnitActions;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnitActionsEditTest extends TestCase
{
    use WithFaker;

    private $unitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitActions = app(UnitActions::class);
    }

    public function test_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Unit::factory(), 'units'), 'companies')
                    ->create();

        $unit = $user->companies->first()->units->first();
        $unitArr = Unit::factory()->make()->toArray();

        $result = $this->unitActions->update($unit, $unitArr);

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'company_id' => $unit->company_id,
            'code' => $unitArr['code'],
            'name' => $unitArr['name'],
        ]);
    }

    public function test_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Unit::factory(), 'units'), 'companies')
                    ->create();

        $units = $user->companies->first()->units->first();
        $unitsArr = [];

        $this->unitActions->update($units, $unitsArr);
    }
}
