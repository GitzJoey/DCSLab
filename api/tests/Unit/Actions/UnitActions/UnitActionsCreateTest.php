<?php

namespace Tests\Unit\Actions\UnitActions;

use App\Actions\Unit\UnitActions;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Tests\ActionsTestCase;

class UnitActionsCreateTest extends ActionsTestCase
{
    private UnitActions $unitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitActions = new UnitActions();
    }

    public function test_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $payload = Unit::factory()->for($company)
            ->make()->toArray();

        $dto = new \App\DTOs\UnitCreateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name'],
            description: $payload['description'],
            type: $payload['type']
        );

        $result = $this->unitActions->create($dto);
        $this->assertDatabaseHas('units', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
            'description' => $payload['description'],
            'type' => $payload['type'],
        ]);
    }

    public function test_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $dto = new \App\DTOs\UnitCreateDTO();

        $this->unitActions->create($dto);
    }
}
