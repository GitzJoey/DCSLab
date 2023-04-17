<?php

namespace Tests\Feature;

use App\Actions\Unit\UnitActions;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnitActionsCreateTest extends TestCase
{
    use WithFaker;

    private $unitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitActions = app(UnitActions::class);
    }

    public function test_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $unitArr = Unit::factory()->make([
            'company_id' => $user->companies->first()->id,
        ])->toArray();

        $result = $this->unitActions->create($unitArr);

        $this->assertDatabaseHas('units', [
            'id' => $result->id,
            'company_id' => $unitArr['company_id'],
            'code' => $unitArr['code'],
            'name' => $unitArr['name'],
            'description' => $unitArr['description'],
            'category' => $unitArr['category'],
        ]);
    }

    public function test_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->unitActions->create([]);
    }
}
