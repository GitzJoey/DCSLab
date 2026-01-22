<?php

namespace App\Http\Controllers;

use App\Actions\CapitalWithdrawal\CapitalWithdrawalActions;
use App\Http\Requests\CapitalWithdrawalRequest;
use App\Http\Resources\CapitalWithdrawalResource;
use App\Models\CapitalWithdrawal;
use Exception;
use Illuminate\Support\Facades\DB;

class CapitalWithdrawalController extends BaseController
{
    private $capitalWithdrawalActions;

    public function __construct(CapitalWithdrawalActions $capitalWithdrawalActions)
    {
        parent::__construct();

        $this->capitalWithdrawalActions = $capitalWithdrawalActions;
    }

    public function store(CapitalWithdrawalRequest $capitalWithdrawalRequest)
    {
        $request = $capitalWithdrawalRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->capitalWithdrawalActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(CapitalWithdrawalRequest $capitalWithdrawalRequest)
    {
        $request = $capitalWithdrawalRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->capitalWithdrawalActions->readAny(
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
            $response = CapitalWithdrawalResource::collection($result);

            return $response;
        }
    }

    public function read(CapitalWithdrawal $capitalWithdrawal, CapitalWithdrawalRequest $capitalWithdrawalRequest)
    {
        $request = $capitalWithdrawalRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->capitalWithdrawalActions->read($capitalWithdrawal);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new CapitalWithdrawalResource($result);

            return $response;
        }
    }

    public function update(CapitalWithdrawal $capitalWithdrawal, CapitalWithdrawalRequest $capitalWithdrawalRequest)
    {
        $request = $capitalWithdrawalRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->capitalWithdrawalActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $capitalWithdrawal->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->capitalWithdrawalActions->update(
                capitalWithdrawal: $capitalWithdrawal,
                data: $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(CapitalWithdrawal $capitalWithdrawal, CapitalWithdrawalRequest $capitalWithdrawalRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->capitalWithdrawalActions->delete($capitalWithdrawal);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
