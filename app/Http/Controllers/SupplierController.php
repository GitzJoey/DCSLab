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

        $userId = Auth::user()->id;
        return $this->SupplierService->read($userId);
    }

    public function getAllSupplier()
    {
        return $this->SupplierService->getAllSupplier();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'suppliers')],
            'name' => 'required|min:3|max:255|alpha',
            'taxable_enterprice' => 'required',
            'status' => 'required'
        ]);

        if ($request['code'] == 'AUTO') {
            $randomGenerator = new randomGenerator();
            $request['code'] = $randomGenerator->generateOne(99999999);
        };

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];
        
        $taxable_enterprice = $request['taxable_enterprice'];
        $taxable_enterprice == 'on' ? $taxable_enterprice = 1 : $taxable_enterprice = 0;

        $result = $this->SupplierService->create(
            $company_id,
            $request['code'],
            $request['name'],
            $request['payment_term_type'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $taxable_enterprice,
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

        $taxable_enterprice = $request['taxable_enterprice'];
        $taxable_enterprice == 'on' ? $taxable_enterprice = 1 : $taxable_enterprice = 0;

        $result = $this->SupplierService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
            $request['payment_term_type'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $taxable_enterprice,
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