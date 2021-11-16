<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductGroupService;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

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
        // if (!parent::hasSelectedCompanyOrCompany() == false) {
        //     return response()->error(trans('error_messages.unable_to_find_selected_company'));
        // }

        // if (!parent::hasSelectedCompanyOrCompany())
        // return response()->error(trans('error_messages.unable_to_find_selected_company'));
        
        $userId = Auth::user()->id;
        return $this->productGroupService->read($userId);
    }

    public function getAllProductGroup()
    {
        return $this->productGroupService->getAllProductGroup();
    }

    public function getAllProduct()
    {
        return $this->productGroupService->getAllProduct();
    }

    public function getAllService()
    {
        return $this->productGroupService->getAllService();
    }

    public function GetAllProductandService()
    {
        return $this->productGroupService->GetAllProductandService();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'productgroups')],
            'name' => 'required|max:255'
        ]);

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $userId = Auth::user()->id;

        $result = $this->productGroupService->create(
            $company_id,
            $request['code'],
            $request['name'],
            $request['category'],
            $userId
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
            Hashids::decode($request['company_id'])[0],
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
