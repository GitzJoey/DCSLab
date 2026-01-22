<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\CashAccount;
use App\Rules\CashAccountStoreValidCode;
use App\Rules\CashAccountStoreValidName;
use App\Rules\CashAccountUpdateValidCode;
use App\Rules\CashAccountUpdateValidName;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CashAccountRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $cashAccount = $this->route('cash_account');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', CashAccount::class) ? true : false;
            case 'read':
                return $user->can('view', CashAccount::class, $cashAccount) ? true : false;
            case 'store':
                return $user->can('create', CashAccount::class) ? true : false;
            case 'update':
                return $user->can('update', CashAccount::class, $cashAccount) ? true : false;
            case 'delete':
                return $user->can('delete', CashAccount::class, $cashAccount) ? true : false;
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
                    'code' => ['required', 'string', 'max:255', new CashAccountStoreValidCode($this->company_id)],
                    'name' => ['required', 'string', 'max:255', new CashAccountStoreValidName($this->company_id)],
                    'is_bank' => ['required', 'boolean'],
                    'is_active' => ['required', 'boolean'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'code' => ['required', 'string', 'max:255', new CashAccountUpdateValidCode($this->company_id, $this->route('cash_account'))],
                    'name' => ['required', 'string', 'max:255', new CashAccountUpdateValidName($this->input('company_id'), $this->route('cash_account'))],
                    'is_bank' => ['required', 'boolean'],
                    'is_active' => ['required', 'boolean'],
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
            'company_id' => trans('validation_attributes.cash_account.company'),
            'branch_id' => trans('validation_attributes.cash_account.branch'),
            'code' => trans('validation_attributes.cash_account.code'),
            'name' => trans('validation_attributes.cash_account.name'),
            'is_bank' => trans('validation_attributes.cash_account.is_bank'),
            'is_active' => trans('validation_attributes.cash_account.is_active'),
            'remarks' => trans('validation_attributes.cash_account.remarks'),
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
                $this->merge([
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
                    'branch_id' => $this->has('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
                    'remarks' => $this->has('remarks') ? $this['remarks'] : null,
                ]);
                break;
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
