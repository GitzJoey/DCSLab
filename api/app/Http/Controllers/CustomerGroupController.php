<?php

namespace App\Http\Controllers;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Http\Requests\CustomerGroupRequest;
use App\Http\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use Exception;

class CustomerGroupController extends BaseController
{
    private $customerGroupActions;

    public function __construct(CustomerGroupActions $customerGroupActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->customerGroupActions = $customerGroupActions;
    }

    public function store(CustomerGroupRequest $customerGroupRequest)
    {
        $request = $customerGroupRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->customerGroupActions->generateUniqueCode();
            } while (! $this->customerGroupActions->isUniqueCode($code, $company_id));
        } else {
            if (! $this->customerGroupActions->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $customerGroupArr = [
            'company_id' => $request['company_id'],
            'code' => $code,
            'name' => $request['name'],
            'max_open_invoice' => $request['max_open_invoice'],
            'max_outstanding_invoice' => $request['max_outstanding_invoice'],
            'max_invoice_age' => $request['max_invoice_age'],
            'payment_term_type' => $request['payment_term_type'],
            'payment_term' => $request['payment_term'],
            'selling_point' => $request['selling_point'],
            'selling_point_multiple' => $request['selling_point_multiple'],
            'sell_at_cost' => $request['sell_at_cost'],
            'price_markup_percent' => $request['price_markup_percent'],
            'price_markup_nominal' => $request['price_markup_nominal'],
            'price_markdown_percent' => $request['price_markdown_percent'],
            'price_markdown_nominal' => $request['price_markdown_nominal'],
            'round_on' => $request['round_on'],
            'round_digit' => $request['round_digit'],
            'remarks' => $request['remarks'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerGroupActions->create($customerGroupArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(CustomerGroupRequest $customerGroupRequest)
    {
        $request = $customerGroupRequest->validated();

        $companyId = $request['company_id'];
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerGroupActions->readAny(
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
            $response = CustomerGroupResource::collection($result);

            return $response;
        }
    }

    public function read(CustomerGroup $customergroup, CustomerGroupRequest $customergroupRequest)
    {
        $request = $customergroupRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerGroupActions->read($customergroup);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new CustomerGroupResource($result);

            return $response;
        }
    }

    public function update(CustomerGroup $customergroup, CustomerGroupRequest $customerGroupRequest)
    {
        $request = $customerGroupRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->customerGroupActions->generateUniqueCode();
            } while (! $this->customerGroupActions->isUniqueCode($code, $company_id, $customergroup->id));
        } else {
            if (! $this->customerGroupActions->isUniqueCode($code, $company_id, $customergroup->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $customerGroupArr = [
            'code' => $code,
            'name' => $request['name'],
            'max_open_invoice' => $request['max_open_invoice'],
            'max_outstanding_invoice' => $request['max_outstanding_invoice'],
            'max_invoice_age' => $request['max_invoice_age'],
            'payment_term_type' => $request['payment_term_type'],
            'payment_term' => $request['payment_term'],
            'selling_point' => $request['selling_point'],
            'selling_point_multiple' => $request['selling_point_multiple'],
            'sell_at_cost' => $request['sell_at_cost'],
            'price_markup_percent' => $request['price_markup_percent'],
            'price_markup_nominal' => $request['price_markup_nominal'],
            'price_markdown_percent' => $request['price_markdown_percent'],
            'price_markdown_nominal' => $request['price_markdown_nominal'],
            'round_on' => $request['round_on'],
            'round_digit' => $request['round_digit'],
            'remarks' => $request['remarks'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerGroupActions->update(
                $customergroup,
                $customerGroupArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(CustomerGroup $customergroup, CustomerGroupRequest $customerGroupRequest)
    {
        $request = $customerGroupRequest->validated();
        
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->customerGroupActions->delete($customergroup);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
