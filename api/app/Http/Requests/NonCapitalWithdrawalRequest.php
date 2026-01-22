<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\NonCapitalWithdrawal;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidNonCapitalWithdrawalCategory;
use App\Rules\NonCapitalWithdrawalStoreValidCode;
use App\Rules\NonCapitalWithdrawalUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NonCapitalWithdrawalRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $nonCapitalWithdrawal = $this->route('non_capital_withdrawal');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', NonCapitalWithdrawal::class) ? true : false;
            case 'read':
                return $user->can('view', NonCapitalWithdrawal::class, $nonCapitalWithdrawal) ? true : false;
            case 'store':
                return $user->can('create', NonCapitalWithdrawal::class) ? true : false;
            case 'update':
                return $user->can('update', NonCapitalWithdrawal::class, $nonCapitalWithdrawal) ? true : false;
            case 'delete':
                return $user->can('delete', NonCapitalWithdrawal::class, $nonCapitalWithdrawal) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return [
                    'refresh' => ['required', 'boolean'],
                    'with_trashed' => ['required', 'boolean'],

                    'search' => ['nullable', 'string'],
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'status' => ['nullable', 'integer', 'in:'.implode(',', RecordStatusEnum::toArrayValue())],

                    'paginate' => ['required', 'boolean'],
                    'page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:1'],
                    'per_page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:10'],
                    'limit' => ['nullable', 'integer', 'min:1'],
                ];
            case 'read':
                return [];
            case 'store':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
                    'code' => ['required', 'string', 'max:255', new NonCapitalWithdrawalStoreValidCode($this->company_id)],
                    'date' => ['required', 'date'],
                    'category_id' => ['required', 'integer', new IsValidNonCapitalWithdrawalCategory($this->company_id)],
                    'cash_account_id' => ['required', 'integer', new IsValidCashAccount($this->company_id)],
                    'amount' => ['required', 'numeric', 'min:0'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
                    'code' => ['required', 'string', 'max:255', new NonCapitalWithdrawalUpdateValidCode($this->company_id, $this->route('non_capital_withdrawal'))],
                    'date' => ['required', 'date'],
                    'category_id' => ['required', 'integer', new IsValidNonCapitalWithdrawalCategory($this->company_id)],
                    'cash_account_id' => ['required', 'integer', new IsValidCashAccount($this->company_id)],
                    'amount' => ['required', 'numeric', 'min:0'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                ];
            case 'delete':
                return [

                ];
            default:
                return [
                    '' => 'required',
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.non_capital_withdrawal.company'),
            'branch_id' => trans('validation_attributes.non_capital_withdrawal.branch'),
            'code' => trans('validation_attributes.non_capital_withdrawal.code'),
            'date' => trans('validation_attributes.non_capital_withdrawal.date'),
            'category_id' => trans('validation_attributes.non_capital_withdrawal.category'),
            'cash_account_id' => trans('validation_attributes.non_capital_withdrawal.cash_account'),
            'amount' => trans('validation_attributes.non_capital_withdrawal.amount'),
            'remarks' => trans('validation_attributes.non_capital_withdrawal.remarks'),
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $this->merge([
                    'with_trashed' => $this->has('with_trashed') ? $this->with_trashed : null,

                    'search' => $this->has('search') ? $this->search : null,
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,

                    'page' => $this->has('page') ? $this->page : null,
                    'per_page' => $this->has('per_page') ? $this->per_page : null,
                    'limit' => $this->has('limit') ? $this->limit : null,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
                    'remarks' => $this->has('remarks') ? $this['remarks'] : null,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
