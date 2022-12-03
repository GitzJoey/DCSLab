<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Models\ChartOfAccount;
use App\Rules\isValidCompany;
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
        if (!Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $chartofaccount = $this->route('chartofaccount');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
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
            'parent_id' => ['nullable'],
            'remarks' =>['nullable', 'max:255'],
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $rules_list = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'perPage' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_list;
            case 'read':
                $rules_read = [
                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:2', 'max:255'],
                    'account_type' => [new Enum(AccountType::class)],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:2', 'max:255'],
                    'account_type' => [new Enum(AccountType::class)],
                ];

                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required',
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.company'),
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
            case 'list':
                $this->merge([
                    'company_id' => $this->has('companyId') ? Hashids::decode($this['companyId'])[0] : '',
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                    'account_type' => AccountType::isValid($this->account_type) ? AccountType::resolveToEnum($this->account_type)->value : -1,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'parent_id' => $this->has('parent_id') ? Hashids::decode($this['parent_id'])[0] : null,
                    'account_type' => AccountType::isValid($this->account_type) ? AccountType::resolveToEnum($this->account_type)->value : -1,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
