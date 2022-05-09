<?php

namespace App\Http\Controllers;

use App\Enums\PaymentTerm;
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
        $search = !is_null($search) ? $search : '';

        $paginate = $request->has('paginate') ? $request['paginate']:true;
        $paginate = !is_null($paginate) ? $paginate : true;
        $paginate = is_numeric($paginate) ? abs($paginate) : true;

        $page = $request->has('page') ? $request['page']:1;
        $page = !is_null($page) ? $page : 1;
        $page = is_numeric($page) ? abs($page) : 1; 

        $perPage = $request->has('perPage') ? $request['perPage']:10;
        $perPage = !is_null($perPage) ? $perPage : 10;
        $perPage = is_numeric($perPage) ? abs($perPage) : 10; 

        $companyId = Hashids::decode($request['companyId'])[0];

        $result = $this->supplierService->read(
            companyId: $companyId,
            search: $search,
            paginate: $paginate,
            page: $page,
            perPage: $perPage
        );

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

        $code = $request['code'] == config('const.DEFAULT.KEYWORDS.AUTO') ? $code = $this->supplierService->generateUniqueCode($company_id) : $request['code'];
        if (!$this->supplierService->isUniqueCode($code, $company_id)) {
            return response()->error([
                'code' => trans('rules.unique_code')
            ]);
        }

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

        return is_null($result) ? response()->error() : response()->success();
    }

    public function update($id, SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();
        $company_id = Hashids::decode($request['company_id'])[0];

        $code = $request['code'] == config('const.DEFAULT.KEYWORDS.AUTO') ? $code = $this->supplierService->generateUniqueCode($company_id) : $request['code'];
        if (!$this->supplierService->isUniqueCode($code, $company_id)) {
            return response()->error([
                'code' => trans('rules.unique_code')
            ]);
        }

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

        return is_null($result) ? response()->error() : response()->success();
    }

    public function delete($id)
    {
        $result = $this->supplierService->delete($id);

        return !$result ? response()->error() : response()->success();
    }

    public function getPaymentTermType()
    {
        return [
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.pia', 'code' => PaymentTerm::PAYMENT_IN_ADVANCE->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.net', 'code' => PaymentTerm::X_DAYS_AFTER_INVOICE->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.eom', 'code' => PaymentTerm::END_OF_MONTH->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cod', 'code' => PaymentTerm::CASH_ON_DELIVERY->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cnd', 'code' => PaymentTerm::CASH_ON_NEXT_DELIVERY->name]
        ];
    }
}
