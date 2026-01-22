<?php

namespace App\Http\Controllers;

use App\Actions\CustomerAddress\CustomerAddressActions;
use App\Http\Requests\CustomerAddressRequest;
use App\Http\Resources\CustomerAddressResource;
use App\Models\CustomerAddress;
use Exception;

class CustomerAddressController extends BaseController
{
    private $customerAddressActions;

    public function __construct(CustomerAddressActions $customerAddressActions)
    {
        parent::__construct();

        $this->customerAddressActions = $customerAddressActions;
    }

    public function store(CustomerAddressRequest $customerAddressRequest)
    {
        $request = $customerAddressRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerAddressActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(CustomerAddressRequest $customerAddressRequest)
    {
        $request = $customerAddressRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerAddressActions->readAny(
                useCache: $request['refresh'],
                withTrashed: $request['with_trashed'],

                search: $request['search'],
                companyId: $request['company_id'],

                paginate: $request['paginate'],
                page: $request['page'],
                perPage: $request['per_page'],
                limit: $request['limit'],
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = CustomerAddressResource::collection($result);

            return $response;
        }
    }

    public function read(CustomerAddress $customerAddress, CustomerAddressRequest $customerAddressRequest)
    {
        $request = $customerAddressRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerAddressActions->read($customerAddress);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new CustomerAddressResource($result);

            return $response;
        }
    }

    public function update(CustomerAddress $customerAddress, CustomerAddressRequest $customerAddressRequest)
    {
        $request = $customerAddressRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueAddress = $this->customerAddressActions->isUniqueAddress(
                companyId: $request['company_id'],
                customerId: $request['customer_id'],
                address: $request['address'],
                exceptId: $customerAddress->id
            );

            if (! $isUniqueAddress) {
                return response()->error(['address' => [trans('rules.unique_address')]], 422);
            }

            $result = $this->customerAddressActions->update(
                customerAddress: $customerAddress,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(CustomerAddress $customerAddress, CustomerAddressRequest $customerAddressRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->customerAddressActions->delete($customerAddress);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
