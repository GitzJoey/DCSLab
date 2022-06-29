<?php

namespace Tests\Feature\API;

use Carbon\Carbon;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyAPITest extends APITestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        Parent::setUp();
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
