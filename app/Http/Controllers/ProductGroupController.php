<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductGroupService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

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

    public function getAllProductGroup()
    {
        return $this->productGroupService->getAllProductGroup();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255|unique:product_groups',
            'name' => 'required|max:255'
        ]);

        $result = $this->productGroupService->create($request['code'], $request['name']);

        if ($result == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode($id, 'product_group'),
            'name' => 'required|max:255',
        ]);
        
        $result = $this->productGroupService->update(
            $id,
            $request['code'],
            $request['name'],
        );

        if ($result == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }

    public function delete($id)
    {
        $result = $this->productGroupService->delete($id);

        if ($result == false) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }
}
