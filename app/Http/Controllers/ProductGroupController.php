<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductGroupService;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Actions\RandomGenerator;
use App\Models\ProductGroup;

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
        if (!parent::hasSelectedCompanyOrCompany() == true) {
            return response()->error(trans('error_messages.unable_to_find_selected_company'));
        }

        return $this->productGroupService->read();
    }

    public function getAllProductGroup_Product()
    {
        return $this->productGroupService->getAllProductGroup_Product();
    }

    public function getAllProductGroup_Service()
    {
        return $this->productGroupService->getAllProductGroup_Service();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'productgroups')],
            'name' => 'required|min:3|max:255',
            'category' => 'required'
        ]);

        $randomGenerator = new randomGenerator();
        $code = $request['code'];
        if ($code == 'AUTO') {
            $code_count = 1;
            do {
                $code = $randomGenerator->generateOne(99999999);
                $code_count = ProductGroup::where('code', $code)->count();
            }
            while ($code_count != 0);
        };

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->productGroupService->create(
            $company_id,
            $code,
            $request['name'],
            $request['category']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'productgroups'),
            'name' => 'required|min:3|max:255',
            'category' => 'required'
        ]);
        
        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->productGroupService->update(
            $id,
            $company_id,
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