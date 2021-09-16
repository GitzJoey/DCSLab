<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductGroupService;
use Illuminate\Http\Request;

class ProductGroupController extends Controller
{
    private $productGroupService;
    private $activityLogService;

    public function __construct(ProductGroupService $productGroupService, ActivityLogService $activityLogService)
    {
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

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'code' => new uniqueCode('create', '', 'productgroups'),
            'name' => 'required|max:255'
        ]);

        $result = $this->productGroupService->create($request['code'], $request['name']);

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
            $request['code'],
            $request['name'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->productGroupService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
