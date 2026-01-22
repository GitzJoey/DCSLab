<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Sale;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidCustomer;
use App\Rules\IsValidWarehouse;
use App\Rules\SaleStoreValidCode;
use App\Rules\SaleUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $sale = $this->route('sale');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', Sale::class) ? true : false;
            case 'read':
                return $user->can('view', Sale::class, $sale) ? true : false;
            case 'store':
                return $user->can('create', Sale::class) ? true : false;
            case 'update':
                return $user->can('update', Sale::class, $sale) ? true : false;
            case 'delete':
                return $user->can('delete', Sale::class, $sale) ? true : false;
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
                    'code' => ['required', 'string', 'max:255', new SaleStoreValidCode($this->company_id)],
                    'date' => ['required', 'date'],
                    'due_days' => ['required', 'integer', 'min:1'],
                    'warehouse_id' => ['required', 'integer', 'bail', new IsValidWarehouse($this->company_id, true)],
                    'customer_id' => ['required', 'integer', 'bail', new IsValidCustomer($this->company_id)],
                    'delivery_note_reference' => ['nullable', 'string', 'max:255'],

                    'tax_invoice_number' => ['nullable', 'string', 'max:255'],
                    'tax_invoice_vat_base' => ['nullable', 'numeric', 'min:0'],
                    'tax_invoice_vat' => ['nullable', 'numeric', 'min:0'],
                    'return_tax_invoice_number' => ['nullable', 'string', 'max:255'],
                    'return_tax_invoice_vat_base' => ['nullable', 'numeric', 'min:0'],
                    'return_tax_invoice_vat' => ['nullable', 'numeric', 'min:0'],

                    'remarks' => ['nullable', 'string', 'max:255'],
                    'is_posted' => ['required', 'boolean'],

                    'total' => ['required', 'numeric', 'min:0'],
                    'global_discount_rate' => ['required', 'numeric', 'min:0'],
                    'global_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'additional_cost' => ['required', 'numeric', 'min:0'],
                    'rounding' => ['required', 'numeric', 'min:0'],
                    'grand_total' => ['required', 'numeric', 'min:0'],

                    'return_total' => ['required', 'numeric', 'min:0'],
                    'return_global_discount_rate' => ['required', 'numeric', 'min:0'],
                    'return_global_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'return_rounding' => ['required', 'numeric', 'min:0'],
                    'return_grand_total' => ['required', 'numeric', 'min:0'],

                    'amount_due' => ['required', 'numeric', 'min:0'],
                    'amount_paid_by_sale_order_down_payment' => ['required', 'numeric', 'min:0'],
                    'amount_paid_by_sale_return' => ['required', 'numeric', 'min:0'],
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
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'code' => ['required', 'string', 'max:255', new SaleUpdateValidCode($this->company_id, $this->route('sale'))],
                    'date' => ['required', 'date'],
                    'due_days' => ['required', 'integer', 'min:1'],
                    'warehouse_id' => ['required', 'integer', 'bail', new IsValidWarehouse($this->company_id, true)],
                    'customer_id' => ['required', 'integer', 'bail', new IsValidCustomer($this->company_id)],
                    'delivery_note_reference' => ['nullable', 'string', 'max:255'],

                    'tax_invoice_number' => ['nullable', 'string', 'max:255'],
                    'tax_invoice_vat_base' => ['nullable', 'numeric', 'min:0'],
                    'tax_invoice_vat' => ['nullable', 'numeric', 'min:0'],
                    'return_tax_invoice_number' => ['nullable', 'string', 'max:255'],
                    'return_tax_invoice_vat_base' => ['nullable', 'numeric', 'min:0'],
                    'return_tax_invoice_vat' => ['nullable', 'numeric', 'min:0'],

                    'remarks' => ['nullable', 'string', 'max:255'],
                    'is_posted' => ['required', 'boolean'],

                    'total' => ['required', 'numeric', 'min:0'],
                    'global_discount_rate' => ['required', 'numeric', 'min:0'],
                    'global_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'additional_cost' => ['required', 'numeric', 'min:0'],
                    'rounding' => ['required', 'numeric', 'min:0'],
                    'grand_total' => ['required', 'numeric', 'min:0'],

                    'return_total' => ['required', 'numeric', 'min:0'],
                    'return_global_discount_rate' => ['required', 'numeric', 'min:0'],
                    'return_global_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'return_rounding' => ['required', 'numeric', 'min:0'],
                    'return_grand_total' => ['required', 'numeric', 'min:0'],

                    'amount_due' => ['required', 'numeric', 'min:0'],
                    'amount_paid_by_sale_order_down_payment' => ['required', 'numeric', 'min:0'],
                    'amount_paid_by_sale_return' => ['required', 'numeric', 'min:0'],
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
            'company_id' => trans('validation_attributes.sale.company'),
            'code' => trans('validation_attributes.sale.code'),
            'remarks' => trans('validation_attributes.sale.remarks'),
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
