<?php

namespace App\Http\Requests\ExpenseCategory;

use App\Enums\ExpenseCategoryTypeEnum;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExpenseCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $expenseCategory = $this->route('expense_category');

        return $user->can('update', $expenseCategory);
    }

    public function prepareForValidation()
    {
        $expenseCategory = $this->route('expense_category');

        $this->merge([
            'company_id' => $expenseCategory?->company_id,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'category_type' => ['present', 'nullable', 'string', Rule::in(ExpenseCategoryTypeEnum::toArray())],
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'sequence' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            /** @var ExpenseCategory|null $expenseCategory */
            $expenseCategory = $this->route('expense_category');

            if (! $expenseCategory || ! is_null($expenseCategory->parent_id)) {
                return;
            }

            if (empty($this->category_type)) {
                $validator->errors()->add('category_type', trans('rules.expense_category.category_type_required_without_parent'));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.expense_category');
    }
}
