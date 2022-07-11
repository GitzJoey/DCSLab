<?php

namespace Tests\Feature\API;

use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
use App\Models\Company;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Database\Seeders\BranchTableSeeder;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class WarehouseAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #region store

    #endregion

    #region list

    #endregion

    #region read

    #endregion

    #region update

    #endregion

    #region delete

    #endregion

    #region others

    #endregion
}
