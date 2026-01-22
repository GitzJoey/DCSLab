<?php

namespace App\Http\Controllers;

use App\Actions\StockTransfer\StockTransferActions;
use App\Http\Requests\StockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Models\StockTransfer;
use Exception;
use Illuminate\Support\Facades\DB;

class StockTransferController extends BaseController
{
    private $stockTransferActions;

    public function __construct(StockTransferActions $stockTransferActions)
    {
        parent::__construct();

        $this->stockTransferActions = $stockTransferActions;
    }

    public function store(StockTransferRequest $stockTransferRequest)
    {
        $request = $stockTransferRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(StockTransferRequest $stockTransferRequest)
    {
        $request = $stockTransferRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferActions->readAny(
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
            $response = StockTransferResource::collection($result);

            return $response;
        }
    }

    public function read(StockTransfer $stockTransfer, StockTransferRequest $stockTransferRequest)
    {
        $request = $stockTransferRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferActions->read($stockTransfer);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new StockTransferResource($result);

            return $response;
        }
    }

    public function update(StockTransfer $stockTransfer, StockTransferRequest $stockTransferRequest)
    {
        $request = $stockTransferRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferActions->update(
                stockTransfer: $stockTransfer,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockTransfer $stockTransfer, StockTransferRequest $stockTransferRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->stockTransferActions->delete($stockTransfer);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
