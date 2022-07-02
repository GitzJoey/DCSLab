<?php

namespace Tests\Feature\API;

use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Profile;
use App\Models\User;
use App\Services\EmployeeService;
use App\Services\RoleService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class EmployeeAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    //region store

    //endregion

    //region list

    //endregion

    //region read

    //endregion

    //region update

    //endregion

    //region delete

    //endregion

    //region others

    //endregion
}
