<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Models\Company;
use App\Rules\deactivateDefaultCompany;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class CompanyRequest extends FormRequest
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
        $company = $this->route('company');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                return $user->can('viewAny', Company::class) ? true : false;
            case 'read':
                return $user->can('view', Company::class, $company) ? true : false;
            case 'store':
                return $user->can('create', Company::class) ? true : false;
            case 'update':
                return $user->can('update', Company::class, $company) ? true : false;
            case 'delete':
                return $user->can('delete', Company::class, $company) ? true : false;
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
        $nullableArr = [
            'address' => ['nullable', 'max:255'],
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $rules_list = [
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'per_page' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_list;
            case 'read':
                $rules_read = [
                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'max:255'],
                    'default' => ['required', 'boolean'],
                    'status' => [new Enum(RecordStatus::class), new deactivateDefaultCompany($this->input('default'), $this->input('status'))],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'max:255'],
                    'default' => ['required', 'boolean'],
                    'status' => [new Enum(RecordStatus::class), new deactivateDefaultCompany($this->input('default'), $this->input('status'))],
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
            'company_id' => trans('validation_attributes.company.company'),
            'code' => trans('validation_attributes.company.code'),
            'name' => trans('validation_attributes.company.name'),
            'address' => trans('validation_attributes.company.address'),
            'default' => trans('validation_attributes.company.default'),
            'status' => trans('validation_attributes.company.status'),
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
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
                $this->merge([
                    'address' => $this->has('address') ? $this['address'] : null,
                    'default' => $this->has('default') ? filter_var($this->default, FILTER_VALIDATE_BOOLEAN) : false,
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                ]);
                break;
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'address' => $this->has('address') ? $this['address'] : null,
                    'default' => $this->has('default') ? filter_var($this->default, FILTER_VALIDATE_BOOLEAN) : false,
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
