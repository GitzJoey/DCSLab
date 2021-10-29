<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductGroupService;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class ProductGroupController extends BaseController
{
    private $productGroupService;
    private $activityLogService;

    public function __construct(ProductGroupService $productGroupService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productGroupService = $productGroupService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('product.groups.index');
    }

    public function read()
    {
        return $this->productGroupService->read();
    }

    public function getAllActiveProductGroup()
    {
        return $this->productGroupService->getAllActiveProductGroup();
    }

    public function getAllProductGroup()
    {
        return $this->productGroupService->getAllProductGroup();
    }

    public function getAllServiceGroup()
    {
        return $this->productGroupService->getAllServiceGroup();
    }

    public function GetAllProductandServiceGroup()
    {
        return $this->productGroupService->GetAllProductandServiceGroup();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'productgroups')],
            'name' => 'required|max:255'
        ]);

        $result = $this->productGroupService->create(
            // Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name'],
            $request['category']
        );

        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'productgroups'),
            'name' => 'required|max:255',
        ]);
        
        $result = $this->productGroupService->update(
            $id,
            // Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name'],
            $request['category']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->productGroupService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
