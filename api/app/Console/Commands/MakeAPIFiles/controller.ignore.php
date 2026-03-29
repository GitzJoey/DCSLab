<?php

namespace App\Http\Controllers;

use App\Actions\RepToPascalThis\RepToPascalThisActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\RepToPascalThis\RepToPascalThisStoreRequest;
use App\Http\Requests\RepToPascalThis\RepToPascalThisUpdateRequest;
use App\Http\Resources\RepToPascalThisResource;
use App\Models\RepToPascalThis;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepToPascalThisController extends BaseController
{
    private $RepToCamelThisActions;

    public function __construct(RepToPascalThisActions $RepToCamelThisActions)
    {
        parent::__construct();

        $this->RepToCamelThisActions = $RepToCamelThisActions;
    }

    public function store(RepToPascalThisStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->RepToCamelThisActions->create($validatedRequest);
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
        $this->authorize('viewAny', RepToPascalThis::class);

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
            $result = $this->RepToCamelThisActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                search: $validatedRequest['search'] ?? null,
                companyId: $validatedRequest['company_id'],
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

        return RepToPascalThisResource::collection($result);
    }

    public function read(RepToPascalThis $RepToCamelThis)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $RepToCamelThis);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->RepToCamelThisActions->read($RepToCamelThis);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new RepToPascalThisResource($result);
    }

    public function update(RepToPascalThis $RepToCamelThis, RepToPascalThisUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->RepToCamelThisActions->update(
                RepToCamelThis: $RepToCamelThis,
                data: $validatedRequest
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(RepToPascalThis $RepToCamelThis)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $RepToCamelThis);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->RepToCamelThisActions->delete($RepToCamelThis);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
