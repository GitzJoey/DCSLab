<?php

namespace App\Http\Controllers;

use App\Actions\SalesOrder\SalesOrderActions;
use App\Http\Requests\SalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use Exception;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends BaseController
{
    private $salesOrderActions;

    public function __construct(SalesOrderActions $salesOrderActions)
    {
        parent::__construct();

        $this->salesOrderActions = $salesOrderActions;
    }

    public function store(SalesOrderRequest $salesOrderRequest)
    {
        $request = $salesOrderRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->salesOrderActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->salesOrderActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SalesOrderRequest $salesOrderRequest)
    {
        $request = $salesOrderRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salesOrderActions->readAny(
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
            $response = SalesOrderResource::collection($result);

            return $response;
        }
    }

    public function read(SalesOrder $salesOrder, SalesOrderRequest $salesOrderRequest)
    {
        $request = $salesOrderRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salesOrderActions->read($salesOrder);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SalesOrderResource($result);

            return $response;
        }
    }

    public function update(SalesOrder $salesOrder, SalesOrderRequest $salesOrderRequest)
    {
        $request = $salesOrderRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salesOrderActions->update(
                salesOrder: $salesOrder,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SalesOrder $salesOrder, SalesOrderRequest $salesOrderRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->salesOrderActions->delete($salesOrder);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
