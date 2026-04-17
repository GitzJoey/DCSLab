<?php

namespace App\Http\Controllers;

use App\Actions\VatProfile\VatProfileActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\VatProfile\VatProfileStoreRequest;
use App\Http\Requests\VatProfile\VatProfileUpdateRequest;
use App\Http\Resources\VatProfileResource;
use App\Models\VatProfile;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VatProfileController extends BaseController
{
    private $vatProfileActions;

    public function __construct(VatProfileActions $vatProfileActions)
    {
        parent::__construct();

        $this->vatProfileActions = $vatProfileActions;
    }

    public function store(VatProfileStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->vatProfileActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->vatProfileActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->vatProfileActions->create([
                'company_id' => $validatedRequest['company_id'],
                'code' => $validatedRequest['code'],
                'name' => $validatedRequest['name'],
                'vat_rate' => $validatedRequest['vat_rate'],
                'vat_base_numerator' => $validatedRequest['vat_base_numerator'],
                'vat_base_denominator' => $validatedRequest['vat_base_denominator'],
                'remarks' => $validatedRequest['remarks'],
                'is_active' => $validatedRequest['is_active'],
            ]);

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
        $this->authorize('viewAny', VatProfile::class);

        if ($request->filled('company_id')) $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        if ($request->filled('include_id')) $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'search' => ['nullable', 'string'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'include_id' => ['nullable', 'integer', 'exists:vat_profiles,id'],

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
            $result = $this->vatProfileActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,

                includeId: $validatedRequest['include_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: $validatedRequest['refresh'],
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
            return VatProfileResource::collection($result);
        }
    }

    public function read(VatProfile $vatProfile)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $vatProfile);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->vatProfileActions->read($vatProfile);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new VatProfileResource($result);
        }
    }

    public function update(VatProfile $vatProfile, VatProfileUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->vatProfileActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $vatProfile->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->vatProfileActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                $vatProfile->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->vatProfileActions->update(
                vatProfile: $vatProfile,
                data: [
                    'code' => $validatedRequest['code'],
                    'name' => $validatedRequest['name'],
                    'vat_rate' => $validatedRequest['vat_rate'],
                    'vat_base_numerator' => $validatedRequest['vat_base_numerator'],
                    'vat_base_denominator' => $validatedRequest['vat_base_denominator'],
                    'remarks' => $validatedRequest['remarks'],
                    'is_active' => $validatedRequest['is_active'],
                ]
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(VatProfile $vatProfile)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $vatProfile);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->vatProfileActions->delete($vatProfile);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
