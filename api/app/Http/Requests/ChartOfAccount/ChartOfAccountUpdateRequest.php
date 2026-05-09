<?php

namespace App\Http\Requests\ChartOfAccount;

use App\Helpers\HashidsHelper;
use App\Models\ChartOfAccount;
use App\Rules\ExistsForCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ChartOfAccountUpdateRequest extends FormRequest
{
    private const ACCOUNT_TYPES = [
        'asset',
        'liability',
        'equity',
        'income',
        'expense',
    ];

    private const NORMAL_BALANCES = [
        'debit',
        'credit',
    ];

    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $chartOfAccount = $this->route('chart_of_account');

        return $user->can('update', $chartOfAccount);
    }

    public function prepareForValidation()
    {
        /** @var ChartOfAccount|null $chartOfAccount */
        $chartOfAccount = $this->route('chart_of_account');

        $this->merge([
            'company_id' => $chartOfAccount?->company_id,
            'parent_id' => $this->filled('parent_id') ? HashidsHelper::decodeId($this->parent_id) : null,
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'parent_id' => ['present', 'nullable', 'integer', new ExistsForCompany('chart_of_accounts', $this->company_id)],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'account_type' => ['required', 'string', Rule::in(self::ACCOUNT_TYPES)],
            'normal_balance' => ['required', 'string', Rule::in(self::NORMAL_BALANCES)],
            'is_group' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            /** @var ChartOfAccount|null $chartOfAccount */
            $chartOfAccount = $this->route('chart_of_account');

            if (! $chartOfAccount) {
                return;
            }

            if ($this->parent_id === $chartOfAccount->id) {
                $validator->errors()->add('parent_id', trans('rules.chart_of_account.parent_must_not_be_self'));
            }

            if (
                ! is_null($this->parent_id)
                && ! $validator->errors()->has('parent_id')
            ) {
                $parent = ChartOfAccount::query()
                    ->where('company_id', $chartOfAccount->company_id)
                    ->where('id', $this->parent_id)
                    ->first();

                if (! $parent?->is_group) {
                    $validator->errors()->add('parent_id', trans('rules.chart_of_account.parent_must_be_group'));
                }

                if ($parent && $this->isDescendantOf($parent, $chartOfAccount->id)) {
                    $validator->errors()->add('parent_id', trans('rules.chart_of_account.parent_must_not_be_descendant'));
                }
            }

            if (! $this->boolean('is_group') && $chartOfAccount->children()->exists()) {
                $validator->errors()->add('is_group', trans('rules.chart_of_account.non_group_must_not_have_children'));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.chart_of_account');
    }

    private function isDescendantOf(ChartOfAccount $candidateParent, int $chartOfAccountId): bool
    {
        $current = $candidateParent;

        while (! is_null($current)) {
            if ($current->id === $chartOfAccountId) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }
}
