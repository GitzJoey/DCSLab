<?php

namespace App\Http\Controllers;

use App\Actions\Role\RoleActions;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use Exception;

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

        try {
            $excludeRole = [];

            $result = $this->roleActions->readAny(exclude: $excludeRole);
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

    public function getAllRoles(RoleRequest $roleRequest)
    {
        $result = null;
        $errorMsg = '';

        try {
            $roles = $this->roleActions->getAllRoles();

            $result = $roles->map(function ($role) {
                return [
                    'code' => $role->name,
                    'name' => $role->display_name,
                ];
            });
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = $result->toArray();

            return $response;
        }
    }
}
