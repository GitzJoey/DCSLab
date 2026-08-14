<?php

namespace App\Http\Requests\ChartOfAccount;

use App\Enums\ChartOfAccountNormalBalanceEnum;
use App\Enums\ChartOfAccountScopeEnum;
use App\Enums\ChartOfAccountSystemKeyEnum;
use App\Helpers\HashidsHelper;
use App\Models\ChartOfAccount;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ChartOfAccountStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', ChartOfAccount::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'parent_id' => $this->filled('parent_id') ? HashidsHelper::decodeId($this->parent_id) : null,
            'scope' => ChartOfAccountScopeEnum::isValid($this->scope) ? ChartOfAccountScopeEnum::resolveToEnum($this->scope)->value : null,
            'system_key' => ChartOfAccountSystemKeyEnum::isValid($this->system_key) ? ChartOfAccountSystemKeyEnum::resolveToEnum($this->system_key)->value : null,
            'source_type' => $this->filled('source_type') ? $this->source_type : null,
            'source_id' => $this->filled('source_id') ? (int) $this->source_id : null,
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'scope' => ['required', new Enum(ChartOfAccountScopeEnum::class)],
            'system_key' => [
                'present',
                'nullable',
                new Enum(ChartOfAccountSystemKeyEnum::class),
                Rule::unique('chart_of_accounts', 'system_key')->where(fn ($query) => $query->where('company_id', $this->company_id)),
            ],
            'parent_id' => ['present', 'nullable', 'integer', new ExistsForCompany('chart_of_accounts', $this->company_id)],
            'source_type' => ['present', 'nullable', 'string', 'max:255'],
            'source_id' => ['present', 'nullable', 'integer', 'min:1'],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'normal_balance' => ['required', new Enum(ChartOfAccountNormalBalanceEnum::class)],
            'is_group' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->scope === ChartOfAccountScopeEnum::SYSTEM->value && is_null($this->system_key)) {
                $validator->errors()->add('system_key', trans('rules.chart_of_account.system_scope_requires_system_key'));
            }

            if ($this->scope === ChartOfAccountScopeEnum::USER->value && ! is_null($this->system_key)) {
                $validator->errors()->add('system_key', trans('rules.chart_of_account.user_scope_must_not_have_system_key'));
            }

            if (is_null($this->source_type) xor is_null($this->source_id)) {
                $validator->errors()->add('source_type', trans('rules.chart_of_account.source_type_and_source_id_must_be_paired'));
                $validator->errors()->add('source_id', trans('rules.chart_of_account.source_type_and_source_id_must_be_paired'));
            }

            if (is_null($this->parent_id)) {
                $validator->errors()->add('parent_id', trans('rules.chart_of_account.parent_is_required_for_account_type'));

                return;
            }

            if (
                $validator->errors()->has('company_id')
                || $validator->errors()->has('parent_id')
            ) {
                return;
            }

            $parent = ChartOfAccount::query()
                ->where('company_id', $this->company_id)
                ->where('id', $this->parent_id)
                ->first();

            if (! $parent?->is_group) {
                $validator->errors()->add('parent_id', trans('rules.chart_of_account.parent_must_be_group'));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.chart_of_account');
    }
}
