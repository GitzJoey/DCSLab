<?php

namespace App\Http\Controllers;

use App\Actions\Investor\InvestorActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Investor\InvestorStoreRequest;
use App\Http\Requests\Investor\InvestorUpdateRequest;
use App\Http\Resources\InvestorResource;
use App\Models\Investor;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvestorController extends BaseController
{
    private $investorActions;

    public function __construct(InvestorActions $investorActions)
    {
        parent::__construct();

        $this->investorActions = $investorActions;
    }

    public function store(InvestorStoreRequest $investorStoreRequest)
    {
        $validatedRequest = $investorStoreRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->investorActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUnique = $this->investorActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null
            );
            if (! $isUnique) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $validatedRequest['remarks'] = $validatedRequest['remarks'] ?? null;

            $result = $this->investorActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('viewAny', Investor::class);

        if ($request->filled('company_id')) {
            $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        }
        if ($request->filled('include_id')) {
            $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);
        }

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],

            'search' => ['nullable', 'string'],
            'include_id' => ['nullable', 'integer', 'exists:investors,id'],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:10'],
        ]);

        $validatedRequest['search'] = $validatedRequest['search'] ?? null;
        $validatedRequest['include_id'] = $validatedRequest['include_id'] ?? null;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->investorActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                search: $validatedRequest['search'] ?? null,
                companyId: $validatedRequest['company_id'],
                includeId: $validatedRequest['include_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: (function () use ($validatedRequest) {
                        $pagination = null;
                        if (isset($validatedRequest['paginate'])) {
                            $pagination = new ExecutePaginationDTO(
                                page: $validatedRequest['paginate']['page'],
                                perPage: $validatedRequest['paginate']['per_page'],
                            );
                        }

                        return $pagination;
                    })(),
                    get: (function () use ($validatedRequest) {
                        $get = null;
                        if (isset($validatedRequest['get'])) {
                            $get = new ExecuteGetDTO(
                                limit: $validatedRequest['get']['limit'],
                            );
                        }

                        return $get;
                    })()
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return InvestorResource::collection($result);
        }
    }

    public function read(Investor $investor)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $investor);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->investorActions->read($investor);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new InvestorResource($result);
        }
    }

    public function update(Investor $investor, InvestorUpdateRequest $investorUpdateRequest)
    {
        $validatedRequest = $investorUpdateRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->investorActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $investor->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUnique = $this->investorActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                $investor->id
            );
            if (! $isUnique) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $validatedRequest['remarks'] = $validatedRequest['remarks'] ?? null;

            $result = $this->investorActions->update(
                investor: $investor,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Investor $investor)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $investor);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->investorActions->delete($investor);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
