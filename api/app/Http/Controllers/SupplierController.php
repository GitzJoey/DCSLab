<?php

namespace App\Http\Controllers;

use App\Actions\Supplier\SupplierActions;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Company;
use App\Models\Supplier;
use Exception;

class SupplierController extends BaseController
{
    private $supplierActions;

    public function __construct(SupplierActions $supplierActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->supplierActions = $supplierActions;
    }

    public function store(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->supplierActions->generateUniqueCode();
            } while (! $this->supplierActions->isUniqueCode($code, $company_id));
        } else {
            if (! $this->supplierActions->isUniqueCode($code, $company_id)) {
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

        $picArr = [];
        if ($request['pic_create_user'] == true) {
            $first_name = '';
            $last_name = '';
            if ($request['pic_contact_person_name'] == trim($request['pic_contact_person_name']) && strpos($request['pic_contact_person_name'], ' ') !== false) {
                $pieces = explode(' ', $request['pic_contact_person_name']);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $request['pic_contact_person_name'];
            }

            $picArr = [
                'name' => $request['pic_contact_person_name'],
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $request['pic_email'],
                'password' => $request['pic_password'],
            ];
        }

        $productsArr = [];
        if (array_key_exists('arr_supplier_product_product_id', $request) && ! empty($request['arr_supplier_product_product_id'])) {
            for ($i = 0; $i < count($request['arr_supplier_product_product_id']); $i++) {
                $product_id = $request['arr_supplier_product_product_id'][$i];
                if (! Company::find($company_id)->products()->where('id', '=', $product_id)->exists()) {
                    return response()->error([
                        'arr_supplier_product_product_id' => [trans('rules.valid_product')],
                    ], 422);
                }

                $mainProduct = 0;
                if (array_key_exists('arr_supplier_product_main_product_id', $request)) {
                    $mainProduct = in_array($product_id, $request['arr_supplier_product_main_product_id']) ? 1 : 0;
                }

                array_push($productsArr, [
                    'product_id' => $product_id,
                    'main_product' => $mainProduct,
                ]);
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierActions->create(
                $supplierArr,
                $picArr,
                $productsArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierActions->readAny(
                companyId: $companyId,
                search: $search,
                paginate: $paginate,
                page: $page,
                perPage: $perPage,
                useCache: $useCache
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
            $result = $this->supplierActions->read($supplier);
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
                $code = $this->supplierActions->generateUniqueCode();
            } while (! $this->supplierActions->isUniqueCode($code, $company_id, $supplier->id));
        } else {
            if (! $this->supplierActions->isUniqueCode($code, $company_id, $supplier->id)) {
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

        $picArr = [];
        if ($request['pic_create_user'] == true && $supplier->user_id == null) {
            $first_name = '';
            $last_name = '';
            if ($request['pic_contact_person_name'] == trim($request['pic_contact_person_name']) && strpos($request['pic_contact_person_name'], ' ') !== false) {
                $pieces = explode(' ', $request['pic_contact_person_name']);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $request['pic_contact_person_name'];
            }

            $picArr = [
                'name' => $request['pic_contact_person_name'],
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $request['pic_email'],
                'password' => $request['pic_password'],
            ];
        }

        $productsArr = [];
        if (array_key_exists('arr_supplier_product_product_id', $request) && ! empty($request['arr_supplier_product_product_id'])) {
            for ($i = 0; $i < count($request['arr_supplier_product_product_id']); $i++) {
                $product_id = $request['arr_supplier_product_product_id'][$i];
                if (! Company::find($company_id)->products()->where('id', '=', $product_id)->exists()) {
                    return response()->error([
                        'arr_supplier_product_product_id' => [trans('rules.valid_product')],
                    ], 422);
                }

                $mainProduct = 0;
                if (array_key_exists('arr_supplier_product_main_product_id', $request)) {
                    $mainProduct = in_array($product_id, $request['arr_supplier_product_main_product_id']) ? 1 : 0;
                }
                array_push($productsArr, [
                    'product_id' => $request['arr_supplier_product_product_id'][$i],
                    'main_product' => $mainProduct,
                ]);
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierActions->update(
                $supplier,
                $supplierArr,
                $picArr,
                $productsArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Supplier $supplier, SupplierRequest $supplierRequest)
    {        
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->supplierActions->delete($supplier);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
