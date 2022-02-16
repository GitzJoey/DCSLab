<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Services\SupplierService;
use Illuminate\Http\Request;

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
        $paginate = $request->has('paginate') ? $request['paginate']:true;
        $perPage = $request->has('perPage') ? $request['perPage']:10;

        $companyId = Hashids::decode($request['companyId'])[0];

        $result = $this->supplierService->read($companyId, $search, $paginate, $perPage);

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = SupplierResource::collection($result);

            return $response;
        }
    }

    public function store(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();
        
        $company_id = Hashids::decode($request['company_id'])[0];

        $code = $request['code'];

        $taxable_enterprise = array_key_exists('taxable_enterprise', $request);

        $poc = [
            'name' => $request['poc_name'],
            'email' => $request['email'], 
        ];

        $supplier_products = [];
        if (!empty($request['productIds'])) {
            for ($i = 0; $i < count($request['productIds']); $i++) {
                array_push($supplier_products, array (
                    'company_id' => $company_id,
                    'product_id' => Hashids::decode($request['productIds'][$i])[0],
                    'main_product' => in_array($request['productIds'][$i], $request['mainProducts']) ? 1 : 0
                ));
            }
        }

        $contact = array_key_exists('contact', $request) ? $request['contact'] : '';

        $result = $this->supplierService->create(
            $company_id,
            $code,
            $request['name'],
            $request['payment_term_type'],
            $request['payment_term'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $taxable_enterprise,
            $request['tax_id'],
            $request['remarks'],
            $request['status'],
            $poc,
            $supplier_products
        );

        return is_null($result) ? response()->error():response()->success();
    }

    public function update($id, SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();
        $company_id = Hashids::decode($request['company_id'])[0];


        $taxable_enterprise = array_key_exists('taxable_enterprise', $request);

        $poc = [

        ];

        $products = [

        ];

        $result = $this->supplierService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
            $request['payment_term_type'],
            $request['payment_term'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $taxable_enterprise,
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
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.net', 'code' => 'NET'],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.eom', 'code' => 'EOM'],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cod', 'code' => 'COD'],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cnd', 'code' => 'CND']            
        ];
    }
}
