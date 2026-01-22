<?php

namespace App\Http\Controllers;

use App\Actions\Company\CompanyActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Company\CompanyStoreRequest;
use App\Http\Requests\Company\CompanyUpdateRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class CompanyController extends BaseController
{
    private $companyActions;

    public function __construct(CompanyActions $companyActions)
    {
        parent::__construct();

        $this->companyActions = $companyActions;
    }

    public function store(CompanyStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] != config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->companyActions->isUniqueCode(
                    Auth::user(), $validatedRequest['code'], null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->companyActions->isUniqueName(
                Auth::user(), $validatedRequest['name'], null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $validatedRequest['address'] = $validatedRequest['address'] ?? null;

            if ($validatedRequest['default']) {
                $this->companyActions->resetDefault(Auth::user());
            }

            $result = $this->companyActions->create(
                user: Auth::user(),
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('viewAny', Company::class);

        if ($request->filled('include_id')) $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);

        if ($request->filled('status')) {
            $request->merge(['status' => RecordStatusEnum::isValid($request->status) ? RecordStatusEnum::resolveToEnum($request->status)->value : -1]);
        }

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'search' => ['nullable', 'string'],
            'default' => ['nullable', 'boolean'],
            'status' => ['nullable', new Enum(RecordStatusEnum::class)],
            'include_id' => ['nullable', 'integer', 'exists:companies,id'],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:10'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->companyActions->readAny(
                user: Auth::user(),
                withTrashed: $validatedRequest['with_trashed'],

                search: $validatedRequest['search'] ?? null,
                default: $validatedRequest['default'] ?? null,
                status: $validatedRequest['status'] ?? null,
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
            return CompanyResource::collection($result);
        }
    }

    public function read(Company $company)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $company);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->companyActions->read($company);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new CompanyResource($result);

            return $response;
        }
    }

    public function update(Company $company, CompanyUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] != config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->companyActions->isUniqueCode(
                    Auth::user(), $validatedRequest['code'], $company->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->companyActions->isUniqueName(
                Auth::user(), $validatedRequest['name'], $company->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $validatedRequest['address'] = $validatedRequest['address'] ?? null;

            if ($validatedRequest['default']) {
                $this->companyActions->resetDefault(Auth::user());
                $company->refresh();
            }

            $result = $this->companyActions->update(
                user: Auth::user(),
                company: $company,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Company $company)
    {
        //Throw Error
        //throw New \Exception('Test Exception From Controller');

        //Throw Empty Response Error (HttpStatus 500)
        //return response()->error();

        //Custom Validation Error 1 Message (HttpStatus 422)
        //return response()->error('Custom Validation Error 1 Message', 422);

        //Custom Validation With Multiple Error (HttpStatus 422)
        //return response()->error(['name' => ['Custom Validation With Multiple Error'], 'address' => ['Custom Validation With Multiple Error']], 422);

        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $company);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($this->companyActions->isDefault($company)) {
                return response()->error(trans('rules.company.delete_default_company'), 422);
            }

            $result = $this->companyActions->delete($company);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
