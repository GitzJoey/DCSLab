<?php

namespace App\Http\Controllers;

use App\Actions\Supplier\SupplierActions;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends BaseController
{
    private $supplierActions;

    public function __construct(SupplierActions $supplierActions)
    {
        parent::__construct();

        $this->supplierActions = $supplierActions;
    }

    public function store(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->supplierActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUniqueCode) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUniqueName = $this->supplierActions->isUniqueName(
                $request['company_id'],
                $request['name'],
                null
            );
            if (! $isUniqueName) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $result = $this->supplierActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SupplierRequest $supplierRequest)
    {
        $request = $supplierRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierActions->readAny(
                user: Auth::user(),
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

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->supplierActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $supplier->id
                );
                if (! $isUniqueCode) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUniqueName = $this->supplierActions->isUniqueName(
                $request['company_id'],
                $request['name'],
                $supplier->id
            );
            if (! $isUniqueName) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $result = $this->supplierActions->update(
                supplier: $supplier,
                data: $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Supplier $supplier, SupplierRequest $supplierRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->supplierActions->delete($supplier);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
