<?php

namespace App\Http\Controllers;

use App\Actions\Role\RoleActions;
use App\Enums\UserRoles;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use Exception;
use Illuminate\Support\Facades\Auth;

class RoleController extends BaseController
{
    private $roleActions;

    public function __construct(RoleActions $roleActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->roleActions = $roleActions;
    }

    public function readAny(RoleRequest $roleRequest)
    {
        $result = null;
        $errorMsg = '';

        $request = $roleRequest->validated();

        try {
            $excludeDevAdminRole = true;

            if ($this->hasAdministratorRole() || $this->hasDeveloperRole()) {
                $excludeDevAdminRole = false;
            }

            $result = $this->roleActions->readAny(excludeDevAdminRole: $excludeDevAdminRole);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = RoleResource::collection($result);

            return $response;
        }
    }

    private function hasAdministratorRole(): bool
    {
        $result = false;

        $usr = Auth::user();
        if (! $usr) {
            return $result;
        }
        if (! $usr->roles->count() == 0) {
            return $result;
        }

        foreach ($usr->roles as $r) {
            if (ucfirst($r->name) == UserRoles::ADMINISTRATOR->value) {
                $result = true;

                return $result;
            }
        }

        return $result;
    }

    private function hasDeveloperRole(): bool
    {
        $result = false;

        $usr = Auth::user();
        if (! $usr) {
            return $result;
        }
        if (! $usr->roles->count() == 0) {
            return $result;
        }

        foreach ($usr->roles as $r) {
            if (ucfirst($r->name) == UserRoles::DEVELOPER->value) {
                $result = true;

                return $result;
            }
        }

        return $result;
    }
}
