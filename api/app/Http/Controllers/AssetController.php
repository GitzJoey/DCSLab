<?php

namespace App\Http\Controllers;

use App\Actions\Asset\AssetActions;
use App\DTOs\AssetCreateDTO;
use App\DTOs\AssetUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Asset\AssetStoreRequest;
use App\Http\Requests\Asset\AssetUpdateRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class AssetController extends BaseController
{
    public function __construct(
        private readonly AssetActions $assetActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', Asset::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'asset_category_id' => $request->filled('asset_category_id') ? HashidsHelper::decodeId($request->asset_category_id) : null,
            'asset_unit_id' => $request->filled('asset_unit_id') ? HashidsHelper::decodeId($request->asset_unit_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'asset_category_id' => ['nullable', 'integer', new ExistsForCompany('asset_categories', $request->company_id)],
            'asset_unit_id' => ['nullable', 'integer', new ExistsForCompany('asset_units', $request->company_id)],
            'status' => ['nullable', 'integer', new Enum(RecordStatusEnum::class)],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('assets', $request->company_id)],
            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->assetActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,
                assetCategoryId: $validatedRequest['asset_category_id'] ?? null,
                assetUnitId: $validatedRequest['asset_unit_id'] ?? null,
                status: $validatedRequest['status'] ?? null,
                includeId: $validatedRequest['include_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate'])
                        ? new ExecutePaginationDTO(
                            page: $validatedRequest['paginate']['page'],
                            perPage: $validatedRequest['paginate']['per_page'],
                        )
                        : null,
                    get: isset($validatedRequest['get'])
                        ? new ExecuteGetDTO(limit: $validatedRequest['get']['limit'])
                        : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : AssetResource::collection($result);
    }

    public function read(Asset $asset)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $asset);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->assetActions->read($asset);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new AssetResource($result);
    }

    public function store(AssetStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->assetActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->assetActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->assetActions->create(
                new AssetCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    assetCategoryId: $validatedRequest['asset_category_id'],
                    code: $validatedRequest['code'],
                    name: $validatedRequest['name'],
                    assetUnitId: $validatedRequest['asset_unit_id'],
                    status: $validatedRequest['status'],
                    remarks: $validatedRequest['remarks'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Asset $asset, AssetUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->assetActions->isUniqueCode(
                    $asset->company_id,
                    $validatedRequest['code'],
                    $asset->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->assetActions->isUniqueName(
                $asset->company_id,
                $validatedRequest['name'],
                $asset->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->assetActions->update(
                asset: $asset,
                data: new AssetUpdateDTO(
                    assetCategoryId: $validatedRequest['asset_category_id'],
                    code: $validatedRequest['code'],
                    name: $validatedRequest['name'],
                    assetUnitId: $validatedRequest['asset_unit_id'],
                    status: $validatedRequest['status'],
                    remarks: $validatedRequest['remarks'],
                ),
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Asset $asset)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $asset);

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->assetActions->delete($asset);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
