<?php

namespace App\Http\Controllers;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\DTOs\ChartOfAccountCreateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\ChartOfAccountNormalBalanceEnum;
use App\Enums\ChartOfAccountScopeEnum;
use App\Enums\ChartOfAccountSystemKeyEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\ChartOfAccount\ChartOfAccountStoreRequest;
use App\Http\Requests\ChartOfAccount\ChartOfAccountUpdateRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\ChartOfAccount;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ChartOfAccountController extends BaseController
{
    private const ACCOUNT_TYPES = [
        'asset',
        'liability',
        'equity',
        'income',
        'expense',
    ];

    public function __construct(
        private readonly ChartOfAccountActions $chartOfAccountActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', ChartOfAccount::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'parent_id' => $request->filled('parent_id') ? HashidsHelper::decodeId($request->parent_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
            'scope' => ChartOfAccountScopeEnum::isValid($request->scope) ? ChartOfAccountScopeEnum::resolveToEnum($request->scope)->value : null,
            'system_key' => ChartOfAccountSystemKeyEnum::isValid($request->system_key) ? ChartOfAccountSystemKeyEnum::resolveToEnum($request->system_key)->value : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],

            'parent_id' => ['nullable', 'integer', new ExistsForCompany('chart_of_accounts', $request->company_id)],
            'has_parent' => ['nullable', 'boolean'],
            'has_children' => ['nullable', 'boolean'],
            'scope' => ['nullable', new Enum(ChartOfAccountScopeEnum::class)],
            'system_key' => ['nullable', new Enum(ChartOfAccountSystemKeyEnum::class)],
            'account_type' => ['nullable', 'string', Rule::in(self::ACCOUNT_TYPES)],
            'normal_balance' => ['nullable', new Enum(ChartOfAccountNormalBalanceEnum::class)],
            'is_group' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('chart_of_accounts', $request->company_id)],

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
            $result = $this->chartOfAccountActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,

                parentId: $validatedRequest['parent_id'] ?? null,
                hasParent: $validatedRequest['has_parent'] ?? null,
                hasChildren: $validatedRequest['has_children'] ?? null,
                scope: $validatedRequest['scope'] ?? null,
                systemKey: $validatedRequest['system_key'] ?? null,
                accountType: $validatedRequest['account_type'] ?? null,
                normalBalance: $validatedRequest['normal_balance'] ?? null,
                isGroup: $validatedRequest['is_group'] ?? null,
                isActive: $validatedRequest['is_active'] ?? null,
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

        return is_null($result) ? response()->error($errorMsg) : ChartOfAccountResource::collection($result);
    }

    public function read(ChartOfAccount $chartOfAccount)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $chartOfAccount);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountActions->read($chartOfAccount);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new ChartOfAccountResource($result);
    }

    public function store(ChartOfAccountStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->chartOfAccountActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            $isUniqueName = $this->chartOfAccountActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->chartOfAccountActions->create(
                new ChartOfAccountCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    scope: $validatedRequest['scope'],
                    systemKey: $validatedRequest['system_key'],
                    parentId: $validatedRequest['parent_id'],
                    sourceType: $validatedRequest['source_type'],
                    sourceId: $validatedRequest['source_id'],
                    code: $validatedRequest['code'],
                    name: $validatedRequest['name'],
                    normalBalance: $validatedRequest['normal_balance'],
                    isGroup: $validatedRequest['is_group'],
                    isActive: $validatedRequest['is_active'],
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

    public function update(ChartOfAccount $chartOfAccount, ChartOfAccountUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->chartOfAccountActions->isUniqueCode(
                $chartOfAccount->company_id,
                $validatedRequest['code'],
                $chartOfAccount->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            $isUniqueName = $this->chartOfAccountActions->isUniqueName(
                $chartOfAccount->company_id,
                $validatedRequest['name'],
                $chartOfAccount->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->chartOfAccountActions->update(
                chartOfAccount: $chartOfAccount,
                data: $validatedRequest,
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(ChartOfAccount $chartOfAccount)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $chartOfAccount);

        if ($this->chartOfAccountActions->hasChildren($chartOfAccount)) {
            return response()->error([
                'errors' => [
                    'chart_of_account' => [trans('rules.chart_of_account.cannot_delete_with_children')],
                ],
                'message' => trans('rules.chart_of_account.cannot_delete_with_children'),
            ], 422);
        }

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->chartOfAccountActions->delete($chartOfAccount);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
