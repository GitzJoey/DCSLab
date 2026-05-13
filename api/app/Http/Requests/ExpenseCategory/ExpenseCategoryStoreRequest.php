<?php

namespace App\Http\Requests\ExpenseCategory;

use App\Enums\ChartOfAccountSystemKeyEnum;
use App\Enums\ExpenseCategoryTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\ChartOfAccount;
use App\Models\ExpenseCategory;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExpenseCategoryStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', ExpenseCategory::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'parent_id' => $this->filled('parent_id') ? HashidsHelper::decodeId($this->parent_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'parent_id' => ['present', 'nullable', 'integer', new ExistsForCompany('expense_categories', $this->company_id)],
            'category_type' => ['present', 'nullable', 'string', Rule::in(ExpenseCategoryTypeEnum::toArray())],
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'sequence' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (
                $validator->errors()->has('company_id')
                || $validator->errors()->has('parent_id')
            ) {
                return;
            }

            if (is_null($this->parent_id)) {
                if (empty($this->category_type)) {
                    $validator->errors()->add('category_type', trans('rules.expense_category.category_type_required_without_parent'));
                }

                return;
            }

            $parentExists = ExpenseCategory::query()
                ->where('company_id', $this->company_id)
                ->where('id', $this->parent_id)
                ->whereNull('deleted_at')
                ->exists();

            if (! $parentExists) {
                $validator->errors()->add('parent_id', trans('rules.expense_category.parent_must_be_active'));

                return;
            }

            $parentChartOfAccountSystemKey = ChartOfAccount::query()
                ->where('company_id', $this->company_id)
                ->where('source_type', ExpenseCategory::class)
                ->where('source_id', $this->parent_id)
                ->with('parent')
                ->first()?->parent?->system_key;

            if ($parentChartOfAccountSystemKey === ChartOfAccountSystemKeyEnum::OTHER_EXPENSE_ROOT->value) {
                $expectedCategoryType = ExpenseCategoryTypeEnum::OTHER_EXPENSE->value;
            } else {
                $expectedCategoryType = ExpenseCategoryTypeEnum::EXPENSE->value;
            }

            if (! empty($this->category_type) && $this->category_type !== $expectedCategoryType) {
                $validator->errors()->add('category_type', trans('rules.expense_category.category_type_must_follow_parent'));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.expense_category');
    }
}
