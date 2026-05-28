<?php

namespace App\Http\Controllers;

use App\Actions\Role\RoleActions;
use App\Enums\UserRole;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RoleController extends BaseController
{
    private RoleActions $roleActions;

    public function __construct(RoleActions $roleActions)
    {
        parent::__construct();

        $this->roleActions = $roleActions;
    }

    public function index(RoleRequest $roleRequest): JsonResponse
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
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            $response = RoleResource::collection($result);

            return $this->apiResponse($response, Response::HTTP_OK);
        }
    }

    private function hasAdministratorRole(): bool
    {
        $result = false;

        $usr = Auth::user();
        if (! $usr) {
            return $result;
        }
        if ($usr->roles->count() == 0) {
            return $result;
        }

        foreach ($usr->roles as $r) {
            if (strtolower($r->name) == UserRole::ADMINISTRATOR->value) {
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
        if ($usr->roles->count() == 0) {
            return $result;
        }

        foreach ($usr->roles as $r) {
            if (strtolower($r->name) == UserRole::DEVELOPER->value) {
                $result = true;

                return $result;
            }
        }

        return $result;
    }
}
