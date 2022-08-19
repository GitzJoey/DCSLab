<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Supplier;
use App\Services\SupplierService;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;

class SupplierController extends BaseController
{
    private $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->supplierService = $supplierService;
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
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $supplierArr = [
            'company_id' => $company_id,
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
            'status' => $request['status'],
        ];

        $first_name = '';
        $last_name = '';
        if ($request['pic_name'] == trim($request['pic_name']) && strpos($request['pic_name'], ' ') !== false) {
            $pieces = explode(' ', $request['pic_name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $request['pic_name'];
        }

        $picArr = [
            'name' => $request['pic_name'],
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $request['email'],
        ];
        
        $productsArr = [];
        if (array_key_exists('productIds', $request) && !empty($request['productIds'])) {
            for ($i = 0; $i < count($request['productIds']); $i++) {
                $mainProduct = 0;
                if (array_key_exists('mainProducts', $request)) {
                    $mainProduct = in_array($request['productIds'][$i], $request['mainProducts']) ? 1 : 0;
                }
                array_push($productsArr, [
                    'company_id' => $company_id,
                    'product_id' => $request['productIds'][$i],
                    'main_product' => $mainProduct,
                ]);
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierService->create(
                $supplierArr,
                $picArr,
                $productsArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function list(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierService->list(
                companyId: $companyId,
                search: $search,
                paginate: $paginate,
                page: $page,
                perPage: $perPage
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
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
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SupplierResource($result);

            return $response;
        }
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
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $supplierArr = [
            'company_id' => $company_id,
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
            'status' => $request['status'],
        ];

        $productsArr = [];
        if (array_key_exists('productIds', $request) && !empty($request['productIds'])) {
            for ($i = 0; $i < count($request['productIds']); $i++) {
                $mainProduct = 0;
                if (array_key_exists('mainProducts', $request)) {
                    $mainProduct = in_array($request['productIds'][$i], $request['mainProducts']) ? 1 : 0;
                }
                array_push($productsArr, [
                    'company_id' => $company_id,
                    'product_id' => $request['productIds'][$i],
                    'main_product' => $mainProduct,
                ]);
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierService->update(
                $supplier,
                $supplierArr,
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
}