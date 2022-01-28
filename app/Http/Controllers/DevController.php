<?php

namespace App\Http\Controllers;

use App\Http\Resources\SupplierResource;
use Illuminate\Http\Request;

use App\Services\SupplierService;
use App\Services\RoleService;

class DevController extends BaseController
{
    private $supplierService;
    private $roleService;

    public function __construct(SupplierService $supplierService, RoleService $roleService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->supplierService = $supplierService;
        $this->roleService = $roleService;
    }

    public function test(Request $request)
    {
        $u = $this->supplierService->read(1, '', true, 10);

        $resource = SupplierResource::collection($u);

        return $resource;
    }
}
