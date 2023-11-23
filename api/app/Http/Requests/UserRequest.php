<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //Authorization Error
        //return false;

        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $authUser = Auth::user();
        $user = $this->route('user');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $authUser->can('viewAny', User::class) ? true : false;
            case 'read':
                return $authUser->can('view', User::class, $user) ? true : false;
            case 'store':
                return $authUser->can('create', User::class) ? true : false;
            case 'update':
                return $authUser->can('update', User::class, $user) ? true : false;
            case 'delete':
                return false;
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
        //Precognition Delayed Validation
        //\Illuminate\Support\Sleep::for(2)->seconds();

        $nullableArr = [
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'postal_code' => 'nullable',
            'img_path' => 'nullable',
            'remarks' => 'nullable',
            'theme' => 'nullable',
            'date_format' => 'nullable',
            'time_format' => 'nullable',
        ];

        if ($this->route()->getActionMethod() == 'readAny') {
            //Validation Error
            /*
            $rules_read_any = [
                'search' => ['required'],
                'paginate' => ['required', 'boolean'],
                'page' => ['required_if:paginate,true', 'numeric'],
                'per_page' => ['required_if:paginate,true', 'numeric'],
                'refresh' => ['nullable', 'boolean'],
            ];
            */

            $rules_read_any = [
                'search' => ['present', 'string'],
                'paginate' => ['required', 'boolean'],
                'page' => ['required_if:paginate,true', 'numeric'],
                'per_page' => ['required_if:paginate,true', 'numeric'],
                'refresh' => ['nullable', 'boolean'],
            ];

            return $rules_read_any;
        } elseif ($this->route()->getActionMethod() == 'read') {
            $rules_read = [

            ];

            return $rules_read;
        } elseif ($this->route()->getActionMethod() == 'store') {
            $rules_store = [
                'name' => ['required', 'alpha_num'],
                'email' => ['required', 'email', 'max:255', 'unique:users'],
                'roles' => ['required'],
                'tax_id' => ['required'],
                'ic_num' => ['required'],
                'status' => [new Enum(RecordStatus::class)],
                'country' => ['required'],
            ];

            return array_merge($rules_store, $nullableArr);
        } elseif ($this->route()->getActionMethod() == 'update') {
            $id = $this->route('user')->id;
            $rules_update = [
                'name' => ['required', 'alpha_num'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
                'roles' => ['required'],
                'tax_id' => ['required'],
                'ic_num' => ['required'],
                'status' => [new Enum(RecordStatus::class)],
                'country' => ['required'],

                'api_token' => ['nullable', 'boolean'],
                'reset_password' => ['nullable', 'boolean'],
                'reset_2fa' => ['nullable', 'boolean'],
            ];

            return array_merge($rules_update, $nullableArr);
        } else {
            return [
                '' => 'required',
            ];
        }
    }

    public function attributes()
    {
        return [
            'name' => trans('validation_attributes.user.name'),
            'email' => trans('validation_attributes.user.email'),
            'roles' => trans('validation_attributes.user.roles'),
            'tax_id' => trans('validation_attributes.user.tax_id'),
            'ic_num' => trans('validation_attributes.user.ic_num'),
            'country' => trans('validation_attributes.user.country'),
            'status' => trans('validation_attributes.user.status'),
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
                    'search' => $this->has('search') && ! is_null($this->search) ? $this->search : '',
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $arr_roles = [];
                if ($this->has('roles')) {
                    for ($i = 0; $i < count($this->roles); $i++) {
                        array_push($arr_roles, $this->roles[$i]['id']);
                    }
                }
                $this->merge(['roles' => $arr_roles]);

                $this->merge([
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
