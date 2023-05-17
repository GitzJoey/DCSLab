<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Models\Employee;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class EmployeeRequest extends FormRequest
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
        $employee = $this->route('employee');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', Employee::class) ? true : false;
            case 'read':
                return $user->can('view', Employee::class, $employee) ? true : false;
            case 'store':
                return $user->can('create', Employee::class) ? true : false;
            case 'update':
                return $user->can('update', Employee::class, $employee) ? true : false;
            case 'delete':
                return $user->can('delete', Employee::class, $employee) ? true : false;
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
            'city' => ['nullable', 'max:255'],
            'postal_code' => ['nullable', 'max:10'],
            'img_path' => ['nullable'],
            'remarks' => ['nullable', 'max:255'],
            'arr_access_branch_id.*' => ['nullable'],
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $rules_read_any = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'per_page' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_read_any;
            case 'read':
                $rules_read = [
                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:3', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                    'country' => ['required'],
                    'tax_id' => ['required'],
                    'ic_num' => ['required', 'min:12', 'max:255'],
                    'join_date' => ['required'],
                    'status' => [new Enum(RecordStatus::class)],
                ];

                $employeeRequest = array_merge($rules_store, $nullableArr);

                return $employeeRequest;
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:3', 'max:255'],
                    'country' => ['required'],
                    'tax_id' => ['required'],
                    'ic_num' => ['required', 'min:12', 'max:255'],
                    'status' => [new Enum(RecordStatus::class)],
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
            'company_id' => trans('validation_attributes.employee.company'),
            'code' => trans('validation_attributes.employee.code'),
            'name' => trans('validation_attributes.employee.name'),
            'email' => trans('validation_attributes.employee.email'),
            'address' => trans('validation_attributes.employee.address'),
            'city' => trans('validation_attributes.employee.city'),
            'postal_code' => trans('validation_attributes.employee.postal_code'),
            'country' => trans('validation_attributes.employee.country'),
            'tax_id' => trans('validation_attributes.employee.tax_id'),
            'ic_num' => trans('validation_attributes.employee.ic_num'),
            'join_date' => trans('validation_attributes.employee.join_date'),
            'remarks' => trans('validation_attributes.employee.remarks'),
            'status' => trans('validation_attributes.employee.status'),
            'arr_access_branch_id.*' => trans('validation_attributes.employee.access_branch.branch'),
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
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
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
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                ]);

                $arr_access_branch_id = [];
                if ($this->has('arr_access_branch_id')) {
                    for ($i = 0; $i < count($this->arr_access_branch_id); $i++) {
                        array_push($arr_access_branch_id, Hashids::decode($this->arr_access_branch_id[$i])[0]);
                    }
                }
                $this->merge(['arr_access_branch_id' => $arr_access_branch_id]);

                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
