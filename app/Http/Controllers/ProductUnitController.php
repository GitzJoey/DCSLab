<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class ProductUnitController extends BaseController
{
    private $productUnitService;
    private $activityLogService;

    public function __construct(ProductUnitService $productUnitService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productUnitService = $productUnitService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('product.productunits.index');
    }

    public function read()
    {
        if (!parent::hasSelectedCompanyOrCompany())
        return response()->error(trans('error_messages.unable_to_find_selected_company'));

        $userId = Auth::user()->id;
        return $this->productUnitService->read($userId);
    }

    public function getAllProductUnit()
    {
        return $this->productUnitService->getAllProductUnit();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'productunits')],
            'name' => 'required|min:3|max:255',
        ]);

        $is_base = $request['is_base'];
        $is_base == 'on' ? $is_base = 1 : $is_base = 0;

        $is_primary_unit = $request['is_primary_unit'];
        $is_primary_unit == 'on' ? $is_primary_unit = 1 : $is_primary_unit = 0;

        $result = $this->productUnitService->create(
            $request['code'],
            Hashids::decode($request['company_id'])[0], 
            Hashids::decode($request['product_id'])[0], 
            Hashids::decode($request['unit_id'])[0], 
            $is_base,
            $request['conversion_value'],
            $is_primary_unit,
            $request['remarks'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'productunits'),
            'name' => 'required|min:3|max:255|alpha_num|alpha_dash',
        ]);
        
        $is_base = $request['is_base'];
        $is_base == 'on' ? $is_base = 1 : $is_base = 0;

        $is_primary_unit = $request['is_primary_unit'];
        $is_primary_unit == 'on' ? $is_primary_unit = 1 : $is_primary_unit = 0;

        $result = $this->productUnitService->update(
            $id,
            $request['code'],
            Hashids::decode($request['company_id'])[0], 
            Hashids::decode($request['product_id'])[0], 
            Hashids::decode($request['unit_id'])[0], 
            $is_base,
            $request['conversion_value'],
            $is_primary_unit,
            $request['remarks'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->productUnitService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}