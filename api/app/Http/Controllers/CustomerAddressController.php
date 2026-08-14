<?php

namespace App\Http\Controllers;

use App\Actions\CustomerAddress\CustomerAddressActions;
use App\DTOs\CustomerAddressCreateDTO;
use App\DTOs\CustomerAddressUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\CustomerAddress\CustomerAddressStoreRequest;
use App\Http\Requests\CustomerAddress\CustomerAddressUpdateRequest;
use App\Http\Resources\CustomerAddressResource;
use App\Models\CustomerAddress;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAddressController extends BaseController
{
    private $customerAddressActions;

    public function __construct(CustomerAddressActions $customerAddressActions)
    {
        parent::__construct();

        $this->customerAddressActions = $customerAddressActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', CustomerAddress::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'include_id' => ['nullable', 'integer', 'exists:customer_addresses,id'],
            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerAddressActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,

                customerId: null,
                includeId: $validatedRequest['include_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: (function () use ($validatedRequest) {
                        $pagination = null;
                        if (isset($validatedRequest['paginate'])) {
                            $pagination = new ExecutePaginationDTO(
                                page: $validatedRequest['paginate']['page'],
                                perPage: $validatedRequest['paginate']['per_page'],
                            );
                        }

                        return $pagination;
                    })(),
                    get: (function () use ($validatedRequest) {
                        $get = null;
                        if (isset($validatedRequest['get'])) {
                            $get = new ExecuteGetDTO(
                                limit: $validatedRequest['get']['limit'],
                            );
                        }

                        return $get;
                    })(),
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return CustomerAddressResource::collection($result);
    }

    public function read(CustomerAddress $customerAddress)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $customerAddress);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerAddressActions->read($customerAddress);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new CustomerAddressResource($result);
    }

    public function store(CustomerAddressStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueAddress = $this->customerAddressActions->isUniqueAddress(
                companyId: $validatedRequest['company_id'],
                customerId: $validatedRequest['customer_id'],
                address: $validatedRequest['address'],
                exceptId: null
            );

            if (! $isUniqueAddress) return response()->error(['address' => [trans('rules.unique_address')]], 422);

            $dto = new CustomerAddressCreateDTO(
                companyId: $validatedRequest['company_id'],
                customerId: $validatedRequest['customer_id'],
                address: $validatedRequest['address'],
                city: $validatedRequest['city'],
                contact: $validatedRequest['contact'],
                isMain: $validatedRequest['is_main'],
                remarks: $validatedRequest['remarks'],
            );
            $result = $this->customerAddressActions->create($dto);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(CustomerAddress $customerAddress, CustomerAddressUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueAddress = $this->customerAddressActions->isUniqueAddress(
                companyId: $validatedRequest['company_id'],
                customerId: $validatedRequest['customer_id'],
                address: $validatedRequest['address'],
                exceptId: $customerAddress->id
            );

            if (! $isUniqueAddress) return response()->error(['address' => [trans('rules.unique_address')]], 422);

            $result = $this->customerAddressActions->update(
                customerAddress: $customerAddress,
                data: new CustomerAddressUpdateDTO(
                    address: $validatedRequest['address'],
                    city: $validatedRequest['city'],
                    contact: $validatedRequest['contact'],
                    isMain: $validatedRequest['is_main'],
                    remarks: $validatedRequest['remarks'],
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(CustomerAddress $customerAddress)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $customerAddress);

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
