<?php

namespace App\Http\Controllers;

use App\Actions\SalesReturn\SalesReturnActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\SalesReturnCreateDTO;
use App\DTOs\SalesReturnUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\SalesReturn\SalesReturnStoreRequest;
use App\Http\Requests\SalesReturn\SalesReturnUpdateRequest;
use App\Http\Resources\SalesReturnResource;
use App\Models\SalesReturn;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends BaseController
{
    public function __construct(
        private readonly SalesReturnActions $salesReturnActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', SalesReturn::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'customer_id' => $request->filled('customer_id') ? HashidsHelper::decodeId($request->customer_id) : null,
            'sales_invoice_id' => $request->filled('sales_invoice_id') ? HashidsHelper::decodeId($request->sales_invoice_id) : null,
            'warehouse_id' => $request->filled('warehouse_id') ? HashidsHelper::decodeId($request->warehouse_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],

            'search' => ['nullable', 'string'],
            'start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'customer_id' => ['nullable', 'integer', new ExistsForCompany('customers', $request->company_id)],
            'sales_invoice_id' => ['nullable', 'integer', new ExistsForCompany('sales_invoices', $request->company_id)],
            'warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'is_settled' => ['nullable', 'boolean'],

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
            $result = $this->salesReturnActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                customerId: $validatedRequest['customer_id'] ?? null,
                salesInvoiceId: $validatedRequest['sales_invoice_id'] ?? null,
                warehouseId: $validatedRequest['warehouse_id'] ?? null,
                isSettled: isset($validatedRequest['is_settled']) ? (bool) $validatedRequest['is_settled'] : null,
                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate'])
                        ? new ExecutePaginationDTO(
                            page: $validatedRequest['paginate']['page'],
                            perPage: $validatedRequest['paginate']['per_page'],
                        )
                        : null,
                    get: isset($validatedRequest['get'])
                        ? new ExecuteGetDTO(limit: $validatedRequest['get']['limit'])
                        : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : SalesReturnResource::collection($result);
    }

    public function read(SalesReturn $salesReturn)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $salesReturn);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salesReturnActions->read($salesReturn);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : new SalesReturnResource($result);
    }

    public function store(SalesReturnStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->salesReturnActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();
            $dto = new SalesReturnCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                customerId: $validatedRequest['customer_id'],
                salesInvoiceId: $validatedRequest['sales_invoice_id'],
                warehouseId: $validatedRequest['warehouse_id'],
                globalDiscount: (float) $validatedRequest['global_discount'],
                rounding: (float) $validatedRequest['rounding'],
                remarks: $validatedRequest['remarks'] ?? null,
                isPosted: $validatedRequest['is_posted'],
                items: $validatedRequest['items'],
                refunds: $validatedRequest['refunds'],
            );
            $result = $this->salesReturnActions->create($dto);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(SalesReturn $salesReturn, SalesReturnUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->salesReturnActions->isUniqueCode(
                    $salesReturn->company_id,
                    $validatedRequest['code'],
                    $salesReturn->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();
            $result = $this->salesReturnActions->update(
                salesReturn: $salesReturn,
                data: new SalesReturnUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    customerId: $validatedRequest['customer_id'],
                    salesInvoiceId: $validatedRequest['sales_invoice_id'],
                    warehouseId: $validatedRequest['warehouse_id'],
                    globalDiscount: (float) $validatedRequest['global_discount'],
                    rounding: (float) $validatedRequest['rounding'],
                    remarks: $validatedRequest['remarks'] ?? null,
                    isPosted: $validatedRequest['is_posted'],
                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],
                    deleteRefundIds: $validatedRequest['delete_refund_ids'],
                    refunds: $validatedRequest['refunds'],
                ),
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SalesReturn $salesReturn)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $salesReturn);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->salesReturnActions->delete($salesReturn);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
