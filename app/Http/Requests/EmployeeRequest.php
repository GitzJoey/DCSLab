<?php

namespace App\Http\Requests;

use App\Enums\ActiveStatus;
use App\Models\Employee;
use App\Rules\isValidCompany;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();

        if ($this->route()->getActionMethod() == 'read' && $user->can('view', $user, Employee::class)) return true;
        if ($this->route()->getActionMethod() == 'store' && $user->can('create', $user, Employee::class)) return true;
        if ($this->route()->getActionMethod() == 'update' && $user->can('update', $user, Employee::class)) return true;
        if ($this->route()->getActionMethod() == 'delete' && $user->can('delete', $user, Employee::class)) return true;

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $nullableArr = [
            'address' => 'nullable|max:255',
            'city' => 'nullable|max:255',
            'postal_code' => 'nullable|max:10',
            'img_path' => 'nullable',
            'remarks' => 'nullable|max:255',
            'accessBranchIds.*' => 'nullable',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'read':
                $rules_read = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'perPage' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean']
                ];
                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|min:3|max:255',
                    'email' => 'required|email|max:255',
                    'country' => 'required',
                    'tax_id' => 'required',
                    'ic_num' => 'required|min:12|max:255',
                    'join_date' => 'required',
                    'status' => [new Enum(ActiveStatus::class)]
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|min:3|max:255',
                    'country' => 'required',
                    'tax_id' => 'required',
                    'ic_num' => 'required|min:12|max:255',
                    'status' => [new Enum(ActiveStatus::class)]
                ];
                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required'
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
        switch($currentRouteMethod) {
            case 'read':
                $this->merge([
                    'company_id' => $this->has('companyId') ? Hashids::decode($this['companyId'])[0] : '',
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'status' => ActiveStatus::isValid($this->status) ? ActiveStatus::fromName($this->status)->value : -1
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
