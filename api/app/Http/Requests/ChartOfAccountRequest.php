<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Enums\RecordStatus;
use App\Models\ChartOfAccount;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class ChartOfAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $chartofaccount = $this->route('chartofaccount');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
            case 'readAnyFormated':
                return $user->can('viewAny', ChartOfAccount::class) ? true : false;
            case 'read':
                return $user->can('view', ChartOfAccount::class, $chartofaccount) ? true : false;
            case 'store':
                return $user->can('create', ChartOfAccount::class) ? true : false;
            case 'update':
                return $user->can('update', ChartOfAccount::class, $chartofaccount) ? true : false;
            case 'delete':
                return $user->can('delete', ChartOfAccount::class, $chartofaccount) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $nullableArr = [
            'remarks' => ['nullable', 'max:255'],
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $rules_read_any = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'branch_id' => ['required'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'per_page' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_read_any;
            case 'readAnyFormated':
                $rules = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'branch_id' => ['required'],
                    'search' => ['present', 'string'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules;
            case 'read':
                $rules_read = [
                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'branch_id' => ['required'],
                    'parent_id' => ['required'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:2', 'max:255'],
                    'account_type' => [new Enum(AccountType::class)],
                    'can_have_child' => ['boolean'],
                    'status' => [new Enum(RecordStatus::class)],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:2', 'max:255'],
                    'account_type' => [new Enum(AccountType::class)],
                    'can_have_child' => ['boolean'],
                    'status' => [new Enum(RecordStatus::class)],
                ];

                return array_merge($rules_update, $nullableArr);

            case 'delete':
                $rules_delete = [

                ];

                return $rules_delete;
            default:
                return [
                    '' => 'required',
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.chart_of_account.company'),
            'branch_id' => trans('validation_attributes.chart_of_account.branch'),
            'parent_id' => trans('validation_attributes.chart_of_account.parent'),
            'code' => trans('validation_attributes.chart_of_account.code'),
            'name' => trans('validation_attributes.chart_of_account.name'),
            'account_type' => trans('validation_attributes.chart_of_account.account_type'),
            'can_have_child' => trans('validation_attributes.chart_of_account.can_have_child'),
            'status' => trans('validation_attributes.chart_of_account.status'),
            'remarks' => trans('validation_attributes.chart_of_account.remarks'),
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
            case 'readAnyFormated':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'branch_id' => $this->has('branch_id') ? Hashids::decode($this['branch_id'])[0] : '',
                    'search' => $this->has('search') && ! is_null($this->search) ? $this->search : '',
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);

                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'branch_id' => $this->has('branch_id') ? Hashids::decode($this['branch_id'])[0] : '',
                    'parent_id' => $this->has('parent_id') ? Hashids::decode($this['parent_id'])[0] : '',
                    'account_type' => AccountType::isValid($this->account_type) ? AccountType::resolveToEnum($this->account_type)->value : -1,
                    'can_have_child' => $this->has('can_have_child') ? filter_var($this->can_have_child, FILTER_VALIDATE_BOOLEAN) : false,
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
