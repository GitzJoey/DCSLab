<?php

namespace App\Http\Controllers;

use App\Actions\SaleProductUnitSerial\SaleProductUnitSerialActions;
use App\Http\Requests\SaleProductUnitSerialRequest;
use App\Http\Resources\SaleProductUnitSerialResource;
use App\Models\SaleProductUnitSerial;
use Exception;

class SaleProductUnitSerialController extends BaseController
{
    private $saleProductUnitSerialActions;

    public function __construct(SaleProductUnitSerialActions $saleProductUnitSerialActions)
    {
        parent::__construct();

        $this->saleProductUnitSerialActions = $saleProductUnitSerialActions;
    }

    public function store(SaleProductUnitSerialRequest $saleProductUnitSerialRequest)
    {
        $request = $saleProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitSerialActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SaleProductUnitSerialRequest $saleProductUnitSerialRequest)
    {
        $request = $saleProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitSerialActions->readAny(
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
            $response = SaleProductUnitSerialResource::collection($result);

            return $response;
        }
    }

    public function read(SaleProductUnitSerial $saleProductUnitSerial, SaleProductUnitSerialRequest $saleProductUnitSerialRequest)
    {
        $request = $saleProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitSerialActions->read($saleProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SaleProductUnitSerialResource($result);

            return $response;
        }
    }

    public function update(SaleProductUnitSerial $saleProductUnitSerial, SaleProductUnitSerialRequest $saleProductUnitSerialRequest)
    {
        $request = $saleProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitSerialActions->update(
                saleProductUnitSerial: $saleProductUnitSerial,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleProductUnitSerial $saleProductUnitSerial, SaleProductUnitSerialRequest $saleProductUnitSerialRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitSerialActions->delete($saleProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
