<?php

namespace App\Http\Controllers;

use App\Enums\PaymentTerm;
use App\Enums\PaymentTermType;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierService;
use Exception;
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

    public function list(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = $this->supplierService->list(
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

    public function read(Supplier $supplier, SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierService->read($supplier);
        } catch(Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }
        
        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = SupplierResource::collection($result);
            return $response;    
        }
    }

    public function store(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->supplierService->generateUniqueCode();
            } while (!$this->supplierService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->supplierService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $supplierArr = [
            'code' => $code,
            'name' => $request['name'],
            'payment_term_type' => $request['payment_term_type'],
            'payment_term' => $request['payment_term'],
            'contact' => $request['contact'],
            'address' => $request['address'],
            'city' => $request['city'],
            'taxable_enterprise' => $request['taxable_enterprise'],
            'tax_id' => $request['tax_id'],
            'remarks' => $request['remarks'],
            'status' => $request['status']
        ];

        $pocArr = [
            'name' => $request['poc_name'],
            'email' => $request['email'], 
        ];

        $productsArr = [];
        if (!empty($request['productIds'])) {
            for ($i = 0; $i < count($request['productIds']); $i++) {
                array_push($productsArr, array (
                    'company_id' => $company_id,
                    'product_id' => Hashids::decode($request['productIds'][$i])[0],
                    'main_product' => in_array($request['productIds'][$i], $request['mainProducts']) ? 1 : 0
                ));
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierService->create(
                $supplierArr,
                $pocArr,
                $productsArr
            );
    
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Supplier $supplier, SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->supplierService->generateUniqueCode();
            } while (!$this->supplierService->isUniqueCode($code, $company_id, $supplier->id));
        } else {
            if (!$this->supplierService->isUniqueCode($code, $company_id, $supplier->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $supplierArr = [
            'code' => $code,
            'name' => $request['name'],
            'payment_term_type' => $request['payment_term_type'],
            'payment_term' => $request['payment_term'],
            'contact' => $request['contact'],
            'address' => $request['address'],
            'city' => $request['city'],
            'taxable_enterprise' => $request['taxable_enterprise'],
            'tax_id' => $request['tax_id'],
            'remarks' => $request['remarks'],
            'status' => $request['status']
        ];

        $pocArr = [
            'name' => $request['poc_name'],
            'email' => $request['email'], 
        ];

        $productsArr = [];
        if (!empty($request['productIds'])) {
            for ($i = 0; $i < count($request['productIds']); $i++) {
                array_push($productsArr, array (
                    'company_id' => $company_id,
                    'product_id' => Hashids::decode($request['productIds'][$i])[0],
                    'main_product' => in_array($request['productIds'][$i], $request['mainProducts']) ? 1 : 0
                ));
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierService->update(
                $supplier,
                $supplierArr,
                $pocArr,
                $productsArr
            );
    
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Supplier $supplier)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->supplierService->delete($supplier);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }

    public function getPaymentTermType()
    {
        return [
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.pia', 'code' => PaymentTermType::PAYMENT_IN_ADVANCE->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.net', 'code' => PaymentTermType::X_DAYS_AFTER_INVOICE->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.eom', 'code' => PaymentTermType::END_OF_MONTH->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cod', 'code' => PaymentTermType::CASH_ON_DELIVERY->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cnd', 'code' => PaymentTermType::CASH_ON_NEXT_DELIVERY->name]
        ];
    }
}
