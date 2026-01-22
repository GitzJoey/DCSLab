<?php

namespace App\Http\Controllers;

use App\Actions\ProductCategory\ProductCategoryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\ProductCategoryTypeEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\ProductCategory\ProductCategoryStoreRequest;
use App\Http\Requests\ProductCategory\ProductCategoryUpdateRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class ProductCategoryController extends BaseController
{
    private $productCategoryActions;

    public function __construct(ProductCategoryActions $productCategoryActions)
    {
        parent::__construct();

        $this->productCategoryActions = $productCategoryActions;
    }

    public function store(ProductCategoryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] != config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->productCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->productCategoryActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->productCategoryActions->create($validatedRequest);

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
        $this->authorize('viewAny', ProductCategory::class);

        if ($request->filled('company_id')) $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        if ($request->filled('include_id')) $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);

        if ($request->filled('type')) {
            $request->merge(['type' => ProductCategoryTypeEnum::isValid($request->type) ? ProductCategoryTypeEnum::resolveToEnum($request->type)->value : -1]);
        }

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'type' => ['nullable', new Enum(ProductCategoryTypeEnum::class)],
            'include_id' => ['nullable', 'integer', 'exists:product_categories,id'],

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
            $result = $this->productCategoryActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],

                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,
                type: $validatedRequest['type'] ?? null,
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
            return ProductCategoryResource::collection($result);
        }
    }

    public function getTypes()
    {
        return [
            ['name' => 'views.product_category.type.product', 'code' => ProductCategoryTypeEnum::PRODUCT->value],
            ['name' => 'views.product_category.type.service', 'code' => ProductCategoryTypeEnum::SERVICE->value],
        ];
    }

    public function read(ProductCategory $productCategory)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $productCategory);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productCategoryActions->read($productCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new ProductCategoryResource($result);
        }
    }

    public function update(ProductCategory $productCategory, ProductCategoryUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->productCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], $productCategory->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->productCategoryActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], $productCategory->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->productCategoryActions->update(
                productCategory: $productCategory,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(ProductCategory $productCategory)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $productCategory);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->productCategoryActions->delete($productCategory);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
