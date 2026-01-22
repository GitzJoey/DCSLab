<?php

namespace App\Http\Controllers;

use App\Actions\RepToPascalThis\RepToPascalThisActions;
use App\Http\Requests\RepToPascalThisRequest;
use App\Http\Resources\RepToPascalThisResource;
use App\Models\RepToPascalThis;
use Exception;

class RepToPascalThisController extends BaseController
{
    private $RepToCamelThisActions;

    public function __construct(RepToPascalThisActions $RepToCamelThisActions)
    {
        parent::__construct();

        $this->RepToCamelThisActions = $RepToCamelThisActions;
    }

    public function store(RepToPascalThisRequest $RepToCamelThisRequest)
    {
        $request = $RepToCamelThisRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->RepToCamelThisActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(RepToPascalThisRequest $RepToCamelThisRequest)
    {
        $request = $RepToCamelThisRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->RepToCamelThisActions->readAny(
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
            $response = RepToPascalThisResource::collection($result);

            return $response;
        }
    }

    public function read(RepToPascalThis $RepToCamelThis, RepToPascalThisRequest $RepToCamelThisRequest)
    {
        $request = $RepToCamelThisRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->RepToCamelThisActions->read($RepToCamelThis);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new RepToPascalThisResource($result);

            return $response;
        }
    }

    public function update(RepToPascalThis $RepToCamelThis, RepToPascalThisRequest $RepToCamelThisRequest)
    {
        $request = $RepToCamelThisRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->RepToCamelThisActions->update(
                RepToCamelThis: $RepToCamelThis,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(RepToPascalThis $RepToCamelThis, RepToPascalThisRequest $RepToCamelThisRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->RepToCamelThisActions->delete($RepToCamelThis);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
