<?php

namespace App\Http\Requests;

use App\Enums\ProductGroupCategory;
use App\Models\ProductGroup;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class ProductGroupRequest extends FormRequest
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
        $productgroup = $this->route('productgroup');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
            case 'getProductGroupDDL':
                return $user->can('viewAny', ProductGroup::class) ? true : false;
            case 'read':
                return $user->can('view', ProductGroup::class, $productgroup) ? true : false;
            case 'store':
                return $user->can('create', ProductGroup::class) ? true : false;
            case 'update':
                return $user->can('update', ProductGroup::class, $productgroup) ? true : false;
            case 'delete':
                return $user->can('delete', ProductGroup::class, $productgroup) ? true : false;
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
        $nullableArr = [];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $rules_read_any = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'category' => ['exclude_if:category,-1', new Enum(ProductGroupCategory::class)],
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
            case 'getProductGroupDDL':
                $rules_get_product_group_ddl = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'category' => ['exclude_if:category,-1', new Enum(ProductGroupCategory::class)],
                ];

                return $rules_get_product_group_ddl;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:2', 'max:255'],
                    'category' => [new Enum(ProductGroupCategory::class)],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:2', 'max:255'],
                    'category' => [new Enum(ProductGroupCategory::class)],
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
            'company_id' => trans('validation_attributes.product_group.company'),
            'code' => trans('validation_attributes.product_group.code'),
            'name' => trans('validation_attributes.product_group.name'),
            'category' => trans('validation_attributes.product_group.category'),
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
                    'category' => ProductGroupCategory::isValid($this->category) ? ProductGroupCategory::resolveToEnum($this->category)->value : -1,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'getProductGroupDDL':
                $category = -1;
                if (isset($this->category)) {
                    $category = ProductGroupCategory::isValid($this->category) ? ProductGroupCategory::resolveToEnum($this->category)->value : 0;
                };

                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',                    
                    'category' => $category,
                ]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'category' => ProductGroupCategory::isValid($this->category) ? ProductGroupCategory::resolveToEnum($this->category)->value : -1,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
