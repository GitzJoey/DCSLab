<?php

namespace App\Http\Controllers;

use App\Actions\NonCapitalWithdrawalCategory\NonCapitalWithdrawalCategoryActions;
use App\Http\Requests\NonCapitalWithdrawalCategoryRequest;
use App\Http\Resources\NonCapitalWithdrawalCategoryResource;
use App\Models\NonCapitalWithdrawalCategory;
use Exception;

class NonCapitalWithdrawalCategoryController extends BaseController
{
    private $nonCapitalWithdrawalCategoryActions;

    public function __construct(NonCapitalWithdrawalCategoryActions $nonCapitalWithdrawalCategoryActions)
    {
        parent::__construct();

        $this->nonCapitalWithdrawalCategoryActions = $nonCapitalWithdrawalCategoryActions;
    }

    public function store(NonCapitalWithdrawalCategoryRequest $nonCapitalWithdrawalCategoryRequest)
    {
        $request = $nonCapitalWithdrawalCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->nonCapitalWithdrawalCategoryActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(NonCapitalWithdrawalCategoryRequest $nonCapitalWithdrawalCategoryRequest)
    {
        $request = $nonCapitalWithdrawalCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->nonCapitalWithdrawalCategoryActions->readAny(
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
            $response = NonCapitalWithdrawalCategoryResource::collection($result);

            return $response;
        }
    }

    public function read(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory, NonCapitalWithdrawalCategoryRequest $nonCapitalWithdrawalCategoryRequest)
    {
        $request = $nonCapitalWithdrawalCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->nonCapitalWithdrawalCategoryActions->read($nonCapitalWithdrawalCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new NonCapitalWithdrawalCategoryResource($result);

            return $response;
        }
    }

    public function update(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory, NonCapitalWithdrawalCategoryRequest $nonCapitalWithdrawalCategoryRequest)
    {
        $request = $nonCapitalWithdrawalCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->nonCapitalWithdrawalCategoryActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $nonCapitalWithdrawalCategory->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUniqueName = $this->nonCapitalWithdrawalCategoryActions->isUniqueName(
                $request['company_id'],
                $request['name'],
                $nonCapitalWithdrawalCategory->id
            );
            if (! $isUniqueName) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $result = $this->nonCapitalWithdrawalCategoryActions->update(
                $nonCapitalWithdrawalCategory,
                $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory, NonCapitalWithdrawalCategoryRequest $nonCapitalWithdrawalCategoryRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->nonCapitalWithdrawalCategoryActions->delete($nonCapitalWithdrawalCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
