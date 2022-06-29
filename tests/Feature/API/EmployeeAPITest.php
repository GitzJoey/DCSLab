<?php

namespace Tests\Feature\API;

use Carbon\Carbon;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Models\Profile;
use App\Enums\UserRoles;
use App\Models\Employee;
use App\Enums\ActiveStatus;
use App\Services\RoleService;
use App\Services\UserService;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Illuminate\Container\Container;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;

class EmployeeAPITest extends APITestCase
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