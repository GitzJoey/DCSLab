<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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

        if (!Auth::check()) return false;

        /** @var \App\User */
        $authUser = Auth::user();
        $user = $this->route('user');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'list':
                return $authUser->can('viewAny', User::class) ? true : false;
            case 'read':
                return $authUser->can('view', User::class, $user) ? true : false;
            case 'store':
                return $authUser->can('create', User::class, $user) ? true : false;
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
        $nullableArr = [
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'postal_code' => 'nullable',
            'img_path' => 'nullable',
            'remarks' => 'nullable',
            'theme' => 'nullable',
            'dateFormat' => 'nullable',
            'timeFormat' => 'nullable',
        ];

        if ($this->route()->getActionMethod() == 'list') {
            $rules_list = [
                'search' => ['present', 'string'],
                'paginate' => ['required', 'boolean'],
                'page' => ['required_if:paginate,true', 'numeric'],
                'perPage' => ['required_if:paginate,true', 'numeric'],
                'refresh' => ['nullable', 'boolean']
            ];
            return $rules_list;
        }
        else if ($this->route()->getActionMethod() == 'read') {
            $rules_read = [

            ];
            return $rules_read;
        }
        else if ($this->route()->getActionMethod() == 'store') {
            $rules_store = [
                //Testing Server Request Validation Error
                //'name' => 'min:1000',
                //'email' => 'min:1000',
                'name' => 'required|alpha_num',
                'email' => 'required|email|max:255|unique:users',
                'roles' => 'required',
                'tax_id' => 'required',
                'ic_num' => 'required',
                'status' => [new Enum(RecordStatus::class)],
                'country' => 'required',
            ];

            return array_merge($rules_store, $nullableArr);
        } else if ($this->route()->getActionMethod() == 'update') {
            $id = $this->route('user')->id;
            $rules_update = [
                'name' => 'required|alpha_num',
                'email' => 'required|email|max:255|unique:users,email,'.$id,
                'roles' => 'required',
                'tax_id' => 'required',
                'ic_num' => 'required',
                'status' => [new Enum(RecordStatus::class)],
                'country' => 'required'
            ];

            return array_merge($rules_update, $nullableArr);
        } else {
            return [
                '' => 'required'
            ];
        }
    }

    public function attributes()
    {
        return [
            'name' => trans('validation_attributes.name'),
            'email' => trans('validation_attributes.email'),
            'roles' => trans('validation_attributes.roles'),
            'tax_id' => trans('validation_attributes.tax_id'),
            'ic_num' => trans('validation_attributes.ic_num'),
            'status' => trans('validation_attributes.status'),
            'country' => trans('validation_attributes.country')
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
            case 'list':
                $this->merge([
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);
                break;
            case 'read';
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::fromName($this->status)->value : -1
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }

    }
}
