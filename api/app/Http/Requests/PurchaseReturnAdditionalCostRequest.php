<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\PurchaseReturnAdditionalCost;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchase;
use App\Rules\IsValidPurchaseReturnAdditionalCostCategory;
use App\Rules\PurchaseReturnAdditionalCostStoreValidCode;
use App\Rules\PurchaseReturnAdditionalCostUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnAdditionalCostRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseReturnAdditionalCost = $this->route('purchase_return_additional_cost');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', PurchaseReturnAdditionalCost::class) ? true : false;
            case 'read':
                return $user->can('view', PurchaseReturnAdditionalCost::class, $purchaseReturnAdditionalCost) ? true : false;
            case 'store':
                return $user->can('create', PurchaseReturnAdditionalCost::class) ? true : false;
            case 'update':
                return $user->can('update', PurchaseReturnAdditionalCost::class, $purchaseReturnAdditionalCost) ? true : false;
            case 'delete':
                return $user->can('delete', PurchaseReturnAdditionalCost::class, $purchaseReturnAdditionalCost) ? true : false;
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
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'purchase_id' => ['required', 'integer', new IsValidPurchase($this->company_id)],
                    'code' => ['required', 'string', 'max:255', new PurchaseReturnAdditionalCostStoreValidCode($this->company_id)],
                    'date' => ['required', 'date'],
                    'category_id' => ['required', 'integer', new IsValidPurchaseReturnAdditionalCostCategory($this->company_id)],
                    'amount' => ['required', 'numeric', 'min:0'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'purchase_id' => ['required', 'integer', new IsValidPurchase($this->company_id)],
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'code' => ['required', 'string', 'max:255', new PurchaseReturnAdditionalCostUpdateValidCode($this->company_id, $this->route('purchase_return_additional_cost'))],
                    'date' => ['required', 'date'],
                    'category_id' => ['required', 'integer', new IsValidPurchaseReturnAdditionalCostCategory($this->company_id)],
                    'amount' => ['required', 'numeric', 'min:0'],
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
            'company_id' => trans('validation_attributes.purchase_return_additional_cost.company'),
            'branch_id' => trans('validation_attributes.purchase_return_additional_cost.branch'),
            'purchase_id' => trans('validation_attributes.purchase_return_additional_cost.purchase'),
            'code' => trans('validation_attributes.purchase_return_additional_cost.code'),
            'date' => trans('validation_attributes.purchase_return_additional_cost.date'),
            'category_id' => trans('validation_attributes.purchase_return_additional_cost.category'),
            'amount' => trans('validation_attributes.purchase_return_additional_cost.amount'),
            'remarks' => trans('validation_attributes.purchase_return_additional_cost.remarks'),
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
                    'remarks' => $this->has('remarks') ? $this['remarks'] : null,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
