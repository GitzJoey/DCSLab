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
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = true;
        $perPage = $request->has('perPage') ? $request['perPage']:null;

        $companyId = Hashids::decode($request['companyId'])[0];

        return $this->supplierService->read($companyId, $search, $paginate, $perPage);
    }

    public function store(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();
        
        $userId = Auth::id();
        $company_id = Hashids::decode($request['company_id'])[0];

        $code = $request['code'];

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
        $company_id = Hashids::decode($request['company_id'])[0];


        $is_tax = array_key_exists('taxable_enterprise', $request);

        $poc = [

        ];

        $products = [

        ];

        $result = $this->supplierService->update(
            $id,
            $request['code'],
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

    public function delete($id)
    {
        $result = $this->supplierService->delete($id);

        return is_null($result) ? response()->error():response()->success();
    }

    public function getPaymentTermType()
    {
        return [
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.pia', 'code' => 'PIA'],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.net30', 'code' => 'NET30'],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.eom', 'code' => 'EOM'],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cod', 'code' => 'COD'],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cnd', 'code' => 'CND']            
        ];
    }
}
