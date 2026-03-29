<?php

namespace App\Http\Controllers;

use App\Actions\SaleReceipt\SaleReceiptActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\SaleReceipt\SaleReceiptStoreRequest;
use App\Http\Requests\SaleReceipt\SaleReceiptUpdateRequest;
use App\Http\Resources\SaleReceiptResource;
use App\Models\SaleReceipt;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleReceiptController extends BaseController
{
    private $saleReceiptActions;

    public function __construct(SaleReceiptActions $saleReceiptActions)
    {
        parent::__construct();

        $this->saleReceiptActions = $saleReceiptActions;
    }

    public function store(SaleReceiptStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->saleReceiptActions->create($validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', SaleReceipt::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,
                saleId: $validatedRequest['sale_id'] ?? null,
                warehouseId: $validatedRequest['warehouse_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate']) ? new ExecutePaginationDTO(
                        page: $validatedRequest['paginate']['page'],
                        perPage: $validatedRequest['paginate']['per_page'],
                    ) : null,
                    get: isset($validatedRequest['get']) ? new ExecuteGetDTO(
                        limit: $validatedRequest['get']['limit'],
                    ) : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return SaleReceiptResource::collection($result);
    }

    public function read(SaleReceipt $saleReceipt)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $saleReceipt);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptActions->read($saleReceipt);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new SaleReceiptResource($result);
    }

    public function update(SaleReceipt $saleReceipt, SaleReceiptUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->saleReceiptActions->update(
                saleReceipt: $saleReceipt,
                data: $validatedRequest
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleReceipt $saleReceipt)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $saleReceipt);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->saleReceiptActions->delete($saleReceipt);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
