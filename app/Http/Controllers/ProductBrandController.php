<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductBrandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
use App\Actions\RandomGenerator;

class ProductBrandController extends BaseController
{
    private $productBrandService;
    private $activityLogService;

    public function __construct(ProductBrandService $productBrandService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productBrandService = $productBrandService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('product.brands.index');
    }

    public function read()
    {
        if (!parent::hasSelectedCompanyOrCompany())
        return response()->error(trans('error_messages.unable_to_find_selected_company'));

        $userId = Auth::user()->id;
        return $this->productBrandService->read($userId);
    }

    public function getAllProductBrand()
    {
        return $this->productBrandService->getAllProductBrand();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'productbrands')],
            'name' => 'required|max:255'
        ]);

        if ($request['code'] == 'AUTO') {
            $randomGenerator = new randomGenerator();
            $request['code'] = $randomGenerator->generateOne(99999999);
        };
        
        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->productBrandService->create(
            $company_id,
            $request['code'],
            $request['name']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'productbrands')],
            'name' => 'required|max:255',
        ]);

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->productBrandService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->productBrandService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
