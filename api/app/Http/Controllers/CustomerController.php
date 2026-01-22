<?php

namespace App\Http\Controllers;

use App\Actions\Customer\CustomerActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Customer\CustomerStoreRequest;
use App\Http\Requests\Customer\CustomerUpdateRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerController extends BaseController
{
    private $customerActions;

    public function __construct(CustomerActions $customerActions)
    {
        parent::__construct();

        $this->customerActions = $customerActions;
    }

    public function store(CustomerStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $validatedRequest['group_id'] = $validatedRequest['group_id'] ?? null;
            $validatedRequest['zone'] = $validatedRequest['zone'] ?? null;
            $validatedRequest['payment_term_type'] = $validatedRequest['payment_term_type'] ?? null;
            $validatedRequest['tax_id'] = $validatedRequest['tax_id'] ?? null;
            $validatedRequest['remarks'] = $validatedRequest['remarks'] ?? null;

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->customerActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUniqueCode) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUniqueName = $this->customerActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null
            );
            if (! $isUniqueName) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $result = $this->customerActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('viewAny', Customer::class);

        if ($request->filled('company_id')) {
            $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        }
        if ($request->filled('group_id')) {
            $request->merge(['group_id' => HashidsHelper::decodeId($request->group_id)]);
        }
        if ($request->filled('include_id')) {
            $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);
        }

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'search' => ['nullable', 'string'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'is_member' => ['nullable', 'boolean'],
            'group_id' => ['nullable', 'integer', new ExistsForCompany('customer_groups', $request->company_id)],
            'zone' => ['nullable', 'string'],
            'max_open_invoice' => ['nullable', 'integer', 'min:0'],
            'max_outstanding_invoice' => ['nullable', 'numeric', 'min:0'],
            'max_invoice_age' => ['nullable', 'integer', 'min:0'],
            'payment_term_type' => ['nullable', 'integer'],
            'payment_term' => ['nullable', 'integer', 'min:0'],
            'taxable_enterprise' => ['nullable', 'boolean'],
            'tax_id' => ['nullable', 'string'],
            'status' => ['nullable', 'integer'],
            'include_id' => ['nullable', 'integer', 'exists:customers,id'],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:10'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $validatedRequest['search'] = $validatedRequest['search'] ?? null;
            $validatedRequest['is_member'] = $validatedRequest['is_member'] ?? null;
            $validatedRequest['group_id'] = $validatedRequest['group_id'] ?? null;
            $validatedRequest['zone'] = $validatedRequest['zone'] ?? null;
            $validatedRequest['max_open_invoice'] = $validatedRequest['max_open_invoice'] ?? null;
            $validatedRequest['max_outstanding_invoice'] = $validatedRequest['max_outstanding_invoice'] ?? null;
            $validatedRequest['max_invoice_age'] = $validatedRequest['max_invoice_age'] ?? null;
            $validatedRequest['payment_term_type'] = $validatedRequest['payment_term_type'] ?? null;
            $validatedRequest['payment_term'] = $validatedRequest['payment_term'] ?? null;
            $validatedRequest['taxable_enterprise'] = $validatedRequest['taxable_enterprise'] ?? null;
            $validatedRequest['tax_id'] = $validatedRequest['tax_id'] ?? null;
            $validatedRequest['status'] = $validatedRequest['status'] ?? null;
            $validatedRequest['include_id'] = $validatedRequest['include_id'] ?? null;

            $result = $this->customerActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,
                isMember: $validatedRequest['is_member'],
                groupId: $validatedRequest['group_id'],
                zone: $validatedRequest['zone'],
                maxOpenInvoice: $validatedRequest['max_open_invoice'],
                maxOutstandingInvoice: $validatedRequest['max_outstanding_invoice'],
                maxInvoiceAge: $validatedRequest['max_invoice_age'],
                paymentTermType: $validatedRequest['payment_term_type'],
                paymentTerm: $validatedRequest['payment_term'],
                taxableEnterprise: $validatedRequest['taxable_enterprise'],
                taxId: $validatedRequest['tax_id'],
                status: $validatedRequest['status'],
                includeId: $validatedRequest['include_id'],

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate']) ? new ExecutePaginationDTO(
                        page: $validatedRequest['paginate']['page'],
                        perPage: $validatedRequest['paginate']['per_page'],
                    ) : null,
                    get: isset($validatedRequest['get']) ? new ExecuteGetDTO(
                        limit: $validatedRequest['get']['limit'],
                    ) : null,
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return CustomerResource::collection($result);
        }
    }

    public function read(Customer $customer)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $customer);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerActions->read($customer);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new CustomerResource($result);
        }
    }

    public function update(Customer $customer, CustomerUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->customerActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $customer->id
                );
                if (! $isUniqueCode) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUniqueName = $this->customerActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                $customer->id
            );
            if (! $isUniqueName) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $validatedRequest['group_id'] = $validatedRequest['group_id'] ?? null;
            $validatedRequest['zone'] = $validatedRequest['zone'] ?? null;
            $validatedRequest['payment_term_type'] = $validatedRequest['payment_term_type'] ?? null;
            $validatedRequest['remarks'] = $validatedRequest['remarks'] ?? null;

            $result = $this->customerActions->update(
                customer: $customer,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Customer $customer)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $customer);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->customerActions->delete($customer);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
