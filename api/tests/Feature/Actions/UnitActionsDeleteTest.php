<?php

namespace Tests\Feature;

use App\Actions\Unit\UnitActions;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnitActionsDeleteTest extends TestCase
{
    use WithFaker;

    private $unitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitActions = app(UnitActions::class);
    }

    public function test_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Unit::factory()->count(5), 'units'), 'companies')
                    ->create();

        $unit = $user->companies->first()->units->first();

        $result = $this->unitActions->delete($unit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('units', [
            'id' => $unit->id,
        ]);
    }
}
