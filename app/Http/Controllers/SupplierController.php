<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
use App\Actions\RandomGenerator;
use App\Models\Supplier;

class SupplierController extends BaseController
{
    private $SupplierService;
    private $activityLogService;

    public function __construct(SupplierService $SupplierService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->SupplierService = $SupplierService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());
        
        return view('purchase.suppliers.index');
    }

    public function read()
    {
        if (!parent::hasSelectedCompanyOrCompany())
        return response()->error(trans('error_messages.unable_to_find_selected_company'));

        return $this->SupplierService->read();
    }

    public function getAllSupplier()
    {
        return $this->SupplierService->getAllSupplier();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'suppliers')],
            'name' => 'required|min:3|max:255',
            'status' => 'required'
        ]);

        $randomGenerator = new randomGenerator();
        $code = $request['code'];
        if ($code == 'AUTO') {
            $code_count = 1;
            do {
                $code = $randomGenerator->generateOne(99999999);
                $code_count = Supplier::where('code', $code)->count();
            }
            while ($code_count != 0);
        };

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];
        
        $taxable_enterprise = $request['taxable_enterprise'];
        $taxable_enterprise == 'on' ? $taxable_enterprise = 1 : $taxable_enterprise = 0;

        $result = $this->SupplierService->create(
            $company_id,
            $code,
            $request['name'],
            $request['payment_term_type'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $taxable_enterprise,
            $request['tax_id'],
            $request['remarks'],
            $request['status']
            );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'suppliers'),
            'name' => 'required|min:3|max:255|alpha',
            'status' => 'required'
        ]);

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $taxable_enterprise = $request['taxable_enterprise'];
        $taxable_enterprise == 'on' ? $taxable_enterprise = 1 : $taxable_enterprise = 0;

        $result = $this->SupplierService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
            $request['payment_term_type'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $taxable_enterprise,
            $request['tax_id'],
            $request['remarks'],
            $request['status'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->SupplierService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}