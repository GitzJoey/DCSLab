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

    public function getRolesDDL()
    {
        $result = [];
        $errorMsg = '';

        try {
            $excludeRole = [];

            $roles = $this->roleActions->readAny(exclude: $excludeRole);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {            
            $roles->map(function ($item) {
                array_push($result, [
                    'code' => $item->ulid, 
                    'name' => $item->display_name]);
            });

            return $result;
        }        
    }
}
