<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Actions\RandomGenerator;
use App\Services\SupplierService;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class SupplierController extends BaseController
{
    private $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->supplierService = $supplierService;
    }

    public function read(Request $request)
    {
        $userId = Auth::user();
        $search = $request->has('search') ? $request['search']:'';
        $paginate = true;
        $perPage = $request->has('perPage') ? $request['perPage']:null;

        $companyIds = $userId->companies()->pluck('company_id');

        return $this->supplierService->read($companyIds, $search, $paginate, $perPage);
    }

    public function store(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();
        
        $userId = Auth::id();
        
        $code = $request['code'];

        if ($code == config()->get('const.KEYWORDS.AUTO'))
            $code = (new RandomGenerator())->generateAlphaNumeric(10);

        $is_tax = array_key_exists('taxable_enterprise', $request);

        $poc = [

        ];

        $products = [

        ];


        $result = $this->supplierService->create(
            Hashids::decode($request['company_id'])[0],
            $code,
            $request['name'],
            $request['payment_term_type'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $is_tax,
            $request['tax_id'],
            $request['remarks'],
            $request['status'],
            $poc,
            $products
        );

        return is_null($result) ? response()->error():response()->success();
    }

    public function update($id, SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $is_tax = $request['is_tax'];
        $is_tax == 'on' ? $is_tax = 1 : $is_tax = 0;

        $result = $this->supplierService->update(
            $id,
            $request['code'],
            $request['name'],
            $request['term'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $is_tax,
            $request['tax_number'],
            $request['remarks'],
            $request['status'],
        );

        return is_null($result) ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->supplierService->delete($id);

        return is_null($result) ? response()->error():response()->success();
    }
}
