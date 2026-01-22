<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Purchase;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchaseOrder;
use App\Rules\IsValidSupplier;
use App\Rules\IsValidWarehouse;
use App\Rules\PurchaseStoreValidCode;
use App\Rules\PurchaseUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchase = $this->route('purchase');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', Purchase::class) ? true : false;
            case 'read':
                return $user->can('view', Purchase::class, $purchase) ? true : false;
            case 'store':
                return $user->can('create', Purchase::class) ? true : false;
            case 'update':
                return $user->can('update', Purchase::class, $purchase) ? true : false;
            case 'delete':
                return $user->can('delete', Purchase::class, $purchase) ? true : false;
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
                    'code' => ['required', 'string', 'max:255', new PurchaseStoreValidCode($this->company_id)],
                    'date' => ['required', 'date'],
                    'due_days' => ['required', 'integer', 'min:0'],
                    'warehouse_id' => ['nullable', 'integer', new IsValidWarehouse($this->company_id, true)],
                    'supplier_id' => ['nullable', 'integer', new IsValidSupplier($this->company_id, true)],
                    'purchase_order_id' => ['nullable', 'integer', new IsValidPurchaseOrder($this->company_id)],
                    'delivery_note_reference' => ['required', 'string', 'max:255'],
                    'purchase_tax_invoice_number' => ['required', 'string', 'max:255'],
                    'purchase_tax_invoice_vat_base' => ['required', 'numeric', 'min:0'],
                    'purchase_tax_invoice_vat' => ['required', 'numeric', 'min:0'],
                    'return_tax_invoice_number' => ['required', 'string', 'max:255'],
                    'return_tax_invoice_vat_base' => ['required', 'numeric', 'min:0'],
                    'return_tax_invoice_vat' => ['required', 'numeric', 'min:0'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                    'is_posted' => ['required', 'boolean'],
                    'purchase_total' => ['required', 'numeric', 'min:0'],
                    'purchase_global_discount_rate' => ['required', 'numeric', 'min:0'],
                    'purchase_global_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'purchase_additional_cost' => ['required', 'numeric', 'min:0'],
                    'purchase_rounding' => ['required', 'numeric', 'min:0'],
                    'purchase_grand_total' => ['required', 'numeric', 'min:0'],
                    'return_total' => ['required', 'numeric', 'min:0'],
                    'return_global_discount_rate' => ['required', 'numeric', 'min:0'],
                    'return_global_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'return_rounding' => ['required', 'numeric', 'min:0'],
                    'return_grand_total' => ['required', 'numeric', 'min:0'],
                    'amount_due' => ['required', 'numeric', 'min:0'],
                    'amount_paid_by_purchase_order_down_payment' => ['required', 'numeric', 'min:0'],
                    'amount_paid_by_purchase_return' => ['required', 'numeric', 'min:0'],
                    'amount_paid_before_invoice' => ['required', 'numeric', 'min:0'],
                    'amount_paid_on_invoice' => ['required', 'numeric', 'min:0'],
                    'amount_paid_after_invoice' => ['required', 'numeric', 'min:0'],
                    'amount_paid_total' => ['required', 'numeric', 'min:0'],
                    'amount_due' => ['required', 'numeric', 'min:0'],
                    'is_paid_off' => ['required', 'boolean'],
                    'is_valid' => ['required', 'boolean'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'code' => ['required', 'string', 'max:255', new PurchaseUpdateValidCode($this->company_id, $this->route('purchase'))],
                    'delivery_note_reference' => ['required', 'string', 'max:255'],
                    'purchase_tax_invoice_number' => ['required', 'string', 'max:255'],
                    'purchase_tax_invoice_vat_base' => ['required', 'numeric', 'min:0'],
                    'purchase_tax_invoice_vat' => ['required', 'numeric', 'min:0'],
                    'return_tax_invoice_number' => ['required', 'string', 'max:255'],
                    'return_tax_invoice_vat_base' => ['required', 'numeric', 'min:0'],
                    'return_tax_invoice_vat' => ['required', 'numeric', 'min:0'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                    'is_posted' => ['required', 'boolean'],
                    'purchase_total' => ['required', 'numeric', 'min:0'],
                    'purchase_global_discount_rate' => ['required', 'numeric', 'min:0'],
                    'purchase_global_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'purchase_additional_cost' => ['required', 'numeric', 'min:0'],
                    'purchase_rounding' => ['required', 'numeric', 'min:0'],
                    'purchase_grand_total' => ['required', 'numeric', 'min:0'],
                    'return_total' => ['required', 'numeric', 'min:0'],
                    'return_global_discount_rate' => ['required', 'numeric', 'min:0'],
                    'return_global_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'return_rounding' => ['required', 'numeric', 'min:0'],
                    'return_grand_total' => ['required', 'numeric', 'min:0'],
                    'amount_due' => ['required', 'numeric', 'min:0'],
                    'amount_paid_by_purchase_order_down_payment' => ['required', 'numeric', 'min:0'],
                    'amount_paid_by_purchase_return' => ['required', 'numeric', 'min:0'],
                    'amount_paid_before_invoice' => ['required', 'numeric', 'min:0'],
                    'amount_paid_on_invoice' => ['required', 'numeric', 'min:0'],
                    'amount_paid_after_invoice' => ['required', 'numeric', 'min:0'],
                    'amount_paid_total' => ['required', 'numeric', 'min:0'],
                    'amount_due' => ['required', 'numeric', 'min:0'],
                    'is_paid_off' => ['required', 'boolean'],
                    'is_valid' => ['required', 'boolean'],
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
            'company_id' => trans('validation_attributes.purchase.company'),
            'code' => trans('validation_attributes.purchase.code'),
            'remarks' => trans('validation_attributes.purchase.remarks'),
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
