<?php

namespace App\Http\Controllers;

use App\Actions\SalesOrderDelivery\SalesOrderDeliveryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\SalesOrderDeliveryCreateDTO;
use App\DTOs\SalesOrderDeliveryUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\SalesOrderDelivery\SalesOrderDeliveryStoreRequest;
use App\Http\Requests\SalesOrderDelivery\SalesOrderDeliveryUpdateRequest;
use App\Http\Resources\SalesOrderDeliveryResource;
use App\Models\SalesOrderDelivery;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderDeliveryController extends BaseController
{
    public function __construct(
        private readonly SalesOrderDeliveryActions $salesOrderDeliveryActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', SalesOrderDelivery::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'customer_id' => $request->filled('customer_id') ? HashidsHelper::decodeId($request->customer_id) : null,
            'sales_order_id' => $request->filled('sales_order_id') ? HashidsHelper::decodeId($request->sales_order_id) : null,
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
            'sales_order_id' => ['nullable', 'integer', new ExistsForCompany('sales_orders', $request->company_id)],
            'warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'is_posted' => ['nullable', 'boolean'],

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
            $result = $this->salesOrderDeliveryActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                customerId: $validatedRequest['customer_id'] ?? null,
                salesOrderId: $validatedRequest['sales_order_id'] ?? null,
                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                warehouseId: $validatedRequest['warehouse_id'] ?? null,
                isPosted: $validatedRequest['is_posted'] ?? null,

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
            : SalesOrderDeliveryResource::collection($result);
    }

    public function read(SalesOrderDelivery $salesOrderDelivery)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $salesOrderDelivery);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salesOrderDeliveryActions->read($salesOrderDelivery);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : new SalesOrderDeliveryResource($result);
    }

    public function store(SalesOrderDeliveryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->salesOrderDeliveryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();
            $dto = new SalesOrderDeliveryCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                customerId: $validatedRequest['customer_id'],
                salesOrderId: $validatedRequest['sales_order_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                warehouseId: $validatedRequest['warehouse_id'],
                remarks: $validatedRequest['remarks'] ?? null,
                isPosted: $validatedRequest['is_posted'],
                items: $validatedRequest['items'],
                costs: $validatedRequest['costs'],
            );
            $result = $this->salesOrderDeliveryActions->create($dto);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(SalesOrderDelivery $salesOrderDelivery, SalesOrderDeliveryUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->salesOrderDeliveryActions->isUniqueCode(
                    $salesOrderDelivery->company_id,
                    $validatedRequest['code'],
                    $salesOrderDelivery->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();
            $result = $this->salesOrderDeliveryActions->update(
                salesOrderDelivery: $salesOrderDelivery,
                data: new SalesOrderDeliveryUpdateDTO(
                    customerId: $validatedRequest['customer_id'],
                    salesOrderId: $validatedRequest['sales_order_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    warehouseId: $validatedRequest['warehouse_id'],
                    remarks: $validatedRequest['remarks'] ?? null,
                    isPosted: $validatedRequest['is_posted'],
                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],
                    deleteCostIds: $validatedRequest['delete_cost_ids'],
                    costs: $validatedRequest['costs'],
                ),
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SalesOrderDelivery $salesOrderDelivery)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $salesOrderDelivery);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->salesOrderDeliveryActions->delete($salesOrderDelivery);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
