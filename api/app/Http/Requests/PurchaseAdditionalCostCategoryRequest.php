<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\PurchaseAdditionalCostCategory;
use App\Rules\IsValidCompany;
use App\Rules\PurchaseAdditionalCostCategoryStoreValidCode;
use App\Rules\PurchaseAdditionalCostCategoryUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseAdditionalCostCategoryRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseAdditionalCostCategory = $this->route('purchase_additional_cost_category');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', PurchaseAdditionalCostCategory::class) ? true : false;
            case 'read':
                return $user->can('view', PurchaseAdditionalCostCategory::class, $purchaseAdditionalCostCategory) ? true : false;
            case 'store':
                return $user->can('create', PurchaseAdditionalCostCategory::class) ? true : false;
            case 'update':
                return $user->can('update', PurchaseAdditionalCostCategory::class, $purchaseAdditionalCostCategory) ? true : false;
            case 'delete':
                return $user->can('delete', PurchaseAdditionalCostCategory::class, $purchaseAdditionalCostCategory) ? true : false;
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
                    'code' => ['required', 'string', 'max:255', new PurchaseAdditionalCostCategoryStoreValidCode($this->company_id)],
                    'name' => ['required', 'string', 'max:255'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'code' => ['required', 'string', 'max:255', new PurchaseAdditionalCostCategoryUpdateValidCode($this->company_id, $this->route('purchase_additional_cost_category'))],
                    'name' => ['required', 'string', 'max:255'],
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
            'company_id' => trans('validation_attributes.purchase_additional_cost_category.company'),
            'code' => trans('validation_attributes.purchase_additional_cost_category.code'),
            'name' => trans('validation_attributes.purchase_additional_cost_category.name'),
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
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
