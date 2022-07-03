<?php

namespace Tests\Feature\API;

use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CompanyAPITest extends APITestCase
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
