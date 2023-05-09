<?php

namespace Tests\Unit;

use App\Actions\Unit\UnitActions;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ActionsTestCase;

class UnitActionsDeleteTest extends ActionsTestCase
{
    use WithFaker;

    private UnitActions $unitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitActions = new UnitActions();
    }

    public function test_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Unit::factory())
            )->create();

        $unit = $user->companies()->inRandomOrder()->first()
            ->units()->inRandomOrder()->first();

        $result = $this->unitActions->delete($unit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('units', [
            'id' => $unit->id,
        ]);
    }
}
