<?php

namespace App\Http\Requests;

use App\Enums\DiscountType;
use App\Enums\RecordStatus;
use App\Models\PurchaseOrder;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderRequest extends FormRequest
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
        $purchaseOrder = $this->route('purchaseorder');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', PurchaseOrder::class) ? true : false;
            case 'read':
                return $user->can('view', PurchaseOrder::class, $purchaseOrder) ? true : false;
            case 'store':
                return $user->can('create', PurchaseOrder::class) ? true : false;
            case 'update':
                return $user->can('update', PurchaseOrder::class, $purchaseOrder) ? true : false;
            case 'delete':
                return $user->can('delete', PurchaseOrder::class, $purchaseOrder) ? true : false;
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
            'shipping_date' => ['nullable', 'date'],
            'shipping_address' => ['nullable', 'max:255'],
            'remarks' => ['nullable', 'max:255'],
            'arr_global_discount_id.*' => ['nullable'],
            'arr_global_discount_discount_type.*' => ['nullable', new Enum(DiscountType::class)],
            'arr_global_discount_amount.*' => ['nullable', 'numeric', 'min:0'],

            'arr_product_unit_id.*' => ['nullable'],

            'arr_product_unit_per_unit_discount_id' => ['nullable', 'array'],
            'arr_product_unit_per_unit_discount_id.*.*' => ['nullable'],
            'arr_product_unit_per_unit_discount_discount_type' => ['nullable', 'array'],
            'arr_product_unit_per_unit_discount_discount_type.*.*' => ['nullable', new Enum(DiscountType::class)],
            'arr_product_unit_per_unit_discount_amount' => ['nullable', 'array'],
            'arr_product_unit_per_unit_discount_amount.*.*' => ['nullable', 'numeric', 'min:0'],

            'arr_product_unit_per_unit_sub_total_discount_id' => ['nullable', 'array'],
            'arr_product_unit_per_unit_sub_total_discount_id.*.*' => ['nullable'],
            'arr_product_unit_per_unit_sub_total_discount_discount_type' => ['nullable', 'array'],
            'arr_product_unit_per_unit_sub_total_discount_discount_type.*.*' => ['nullable', new Enum(DiscountType::class)],
            'arr_product_unit_per_unit_sub_total_discount_amount' => ['nullable', 'array'],
            'arr_product_unit_per_unit_sub_total_discount_amount.*.*' => ['nullable', 'numeric', 'min:0'],

            'arr_product_unit_remarks.*' => ['nullable', 'max:255'],
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $rules_read_any = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'branch_id' => ['required'],
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
                    'branch_id' => ['required'],
                    'invoice_code' => ['required', 'max:255'],
                    'invoice_date' => ['required', 'date'],
                    'supplier_id' => ['required'],
                    'status' => [new Enum(RecordStatus::class)],
                    'arr_product_unit_product_unit_id.*' => ['required'],
                    'arr_product_unit_qty.*' => ['numeric', 'min:0.01'],
                    'arr_product_unit_amount_per_unit.*' => ['numeric', 'min:1'],
                    'arr_product_unit_initial_price.*' => ['numeric', 'min:0'],
                    'arr_product_unit_vat_status.*' => ['required'],
                    'arr_product_unit_vat_rate.*' => ['required', 'numeric', 'between:0,1'],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'branch_id' => ['required'],
                    'invoice_code' => ['required', 'max:255'],
                    'invoice_date' => ['required'],
                    'supplier_id' => ['required'],
                    'status' => [new Enum(RecordStatus::class)],
                    'arr_product_unit_product_unit_id.*' => ['required'],
                    'arr_product_unit_qty.*' => ['numeric', 'min:0.01'],
                    'arr_product_unit_amount_per_unit.*' => ['numeric', 'min:1'],
                    'arr_product_unit_initial_price.*' => ['numeric', 'min:0'],
                    'arr_product_unit_vat_status.*' => ['required'],
                    'arr_product_unit_vat_rate.*' => ['required', 'numeric', 'between:0,1'],
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
            'company_id' => trans('validation_attributes.purchase_order.company'),
            'branch_id' => trans('validation_attributes.purchase_order.branch'),
            'invoice_code' => trans('validation_attributes.purchase_order.invoice_code'),
            'invoice_date' => trans('validation_attributes.purchase_order.invoice_date'),
            'supplier_id' => trans('validation_attributes.purchase_order.supplier'),
            'shipping_date' => trans('validation_attributes.purchase_order.shipping_date'),
            'shipping_address' => trans('validation_attributes.purchase_order.shipping_address'),
            'remarks' => trans('validation_attributes.purchase_order.remarks'),
            'status' => trans('validation_attributes.purchase_order.status'),
            'arr_global_discount_id.*' => trans('validation_attributes.purchase_order.global_discount.id'),
            'arr_global_discount_discount_type.*' => trans('validation_attributes.purchase_order.global_discount.discount_type'),
            'arr_global_discount_amount.*' => trans('validation_attributes.purchase_order.global_discount.amount'),
            'arr_product_unit_id.*' => trans('validation_attributes.purchase_order.product_units.id'),
            'arr_product_unit_product_unit_id.*' => trans('validation_attributes.purchase_order.product_units.product_unit'),
            'arr_product_unit_qty.*' => trans('validation_attributes.purchase_order.product_units.qty'),
            'arr_product_unit_amount_per_unit.*' => trans('validation_attributes.purchase_order.product_units.amount_per_unit'),
            'arr_product_unit_initial_price.*' => trans('validation_attributes.purchase_order.product_units.initial_price'),
            'arr_product_unit_per_unit_discount_id.*' => trans('validation_attributes.purchase_order.product_units.per_unit_discount.id'),
            'arr_product_unit_per_unit_discount_discount_type.*' => trans('validation_attributes.purchase_order.product_units.per_unit_discount.discount_type'),
            'arr_product_unit_per_unit_discount_amount.*' => trans('validation_attributes.purchase_order.product_units.per_unit_discount.amount'),
            'arr_product_unit_per_unit_sub_total_discount_id.*' => trans('validation_attributes.purchase_order.product_units.per_unit_sub_total_discount.id'),
            'arr_product_unit_per_unit_sub_total_discount_discount_type.*' => trans('validation_attributes.purchase_order.product_units.per_unit_sub_total_discount.discount_type'),
            'arr_product_unit_per_unit_sub_total_discount_amount.*' => trans('validation_attributes.purchase_order.product_units.per_unit_sub_total_discount.amount'),
            'arr_product_unit_vat_status.*' => trans('validation_attributes.purchase_order.product_units.vat_status'),
            'arr_product_unit_vat_rate.*' => trans('validation_attributes.purchase_order.product_units.vat_rate'),
            'arr_product_unit_remarks.*' => trans('validation_attributes.purchase_order.product_units.remarks'),
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
                    'branch_id' => $this->has('branch_id') ? Hashids::decode($this['branch_id'])[0] : '',
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
                    'branch_id' => $this->has('branch_id') ? Hashids::decode($this['branch_id'])[0] : '',
                    'supplier_id' => $this->has('supplier_id') ? Hashids::decode($this['supplier_id'])[0] : '',
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                ]);

                $arr_global_discount_id = [];
                if ($this->has('arr_global_discount_id')) {
                    for ($i = 0; $i < count($this->arr_global_discount_id); $i++) {
                        if ($this->arr_global_discount_id[$i] != '') {
                            array_push($arr_global_discount_id, Hashids::decode($this->arr_global_discount_id[$i])[0]);
                        } else {
                            array_push($arr_global_discount_id, null);
                        }
                    }
                }
                $this->merge(['arr_global_discount_id' => $arr_global_discount_id]);

                $arr_global_discount_discount_type = [];
                if ($this->has('arr_global_discount_discount_type')) {
                    for ($i = 0; $i < count($this->arr_global_discount_discount_type); $i++) {
                        if ($this->arr_global_discount_discount_type[$i] != '') {
                            $result = DiscountType::isValid($this->arr_global_discount_discount_type[$i]) ? DiscountType::resolveToEnum($this->arr_global_discount_discount_type[$i]) : -1;
                            array_push($arr_global_discount_discount_type, $result);
                        } else {
                            array_push($arr_global_discount_discount_type, -1);
                        }
                    }
                }
                $this->merge(['arr_global_discount_discount_type' => $arr_global_discount_discount_type]);

                $arr_global_discount_amount = [];
                if ($this->has('arr_global_discount_amount')) {
                    for ($i = 0; $i < count($this->arr_global_discount_amount); $i++) {
                        if ($this->arr_global_discount_amount[$i] != '') {
                            array_push($arr_global_discount_amount, $this->arr_global_discount_amount[$i]);
                        } else {
                            array_push($arr_global_discount_amount, 0);
                        }
                    }
                }
                $this->merge(['arr_global_discount_amount' => $arr_global_discount_amount]);

                $arr_product_unit_id = [];
                if ($this->has('arr_product_unit_id')) {
                    for ($i = 0; $i < count($this->arr_product_unit_id); $i++) {
                        if ($this->arr_product_unit_id[$i] != '') {
                            array_push($arr_product_unit_id, Hashids::decode($this->arr_product_unit_id[$i])[0]);
                        } else {
                            array_push($arr_product_unit_id, null);
                        }
                    }
                }
                $this->merge(['arr_product_unit_id' => $arr_product_unit_id]);

                $arr_product_unit_product_unit_id = [];
                if ($this->has('arr_product_unit_product_unit_id')) {
                    for ($i = 0; $i < count($this->arr_product_unit_product_unit_id); $i++) {
                        if ($this->arr_product_unit_product_unit_id[$i] != '') {
                            array_push($arr_product_unit_product_unit_id, Hashids::decode($this->arr_product_unit_product_unit_id[$i])[0]);
                        } else {
                            array_push($arr_product_unit_product_unit_id, null);
                        }
                    }
                }
                $this->merge(['arr_product_unit_product_unit_id' => $arr_product_unit_product_unit_id]);

                $arr_product_unit_qty = [];
                if ($this->has('arr_product_unit_qty')) {
                    for ($i = 0; $i < count($this->arr_product_unit_qty); $i++) {
                        if ($this->arr_product_unit_qty[$i] != '') {
                            array_push($arr_product_unit_qty, $this->arr_product_unit_qty[$i]);
                        } else {
                            array_push($arr_product_unit_qty, 0);
                        }
                    }
                }
                $this->merge(['arr_product_unit_qty' => $arr_product_unit_qty]);

                $arr_product_unit_amount_per_unit = [];
                if ($this->has('arr_product_unit_amount_per_unit')) {
                    for ($i = 0; $i < count($this->arr_product_unit_amount_per_unit); $i++) {
                        if ($this->arr_product_unit_amount_per_unit[$i] != '') {
                            array_push($arr_product_unit_amount_per_unit, $this->arr_product_unit_amount_per_unit[$i]);
                        } else {
                            array_push($arr_product_unit_amount_per_unit, 0);
                        }
                    }
                }
                $this->merge(['arr_product_unit_amount_per_unit' => $arr_product_unit_amount_per_unit]);

                $arr_product_unit_initial_price = [];
                if ($this->has('arr_product_unit_initial_price')) {
                    for ($i = 0; $i < count($this->arr_product_unit_initial_price); $i++) {
                        if ($this->arr_product_unit_initial_price[$i] != '') {
                            array_push($arr_product_unit_initial_price, $this->arr_product_unit_initial_price[$i]);
                        } else {
                            array_push($arr_product_unit_initial_price, 0);
                        }
                    }
                }
                $this->merge(['arr_product_unit_initial_price' => $arr_product_unit_initial_price]);

                $arr_product_unit_per_unit_discount_id = [];
                if ($this->has('arr_product_unit_per_unit_discount_id')) {
                    foreach ($this->arr_product_unit_per_unit_discount_id as $parentIdx => $parentResult) {
                        foreach ($parentResult as $idx => $result) {
                            if ($result != '') {
                                $arr_product_unit_per_unit_discount_id[$parentIdx][$idx] = Hashids::decode($result)[0];
                            } else {
                                $arr_product_unit_per_unit_discount_id[$parentIdx][$idx] = null;
                            }
                        }
                    }
                }
                $this->merge(['arr_product_unit_per_unit_discount_id' => $arr_product_unit_per_unit_discount_id]);

                $arr_product_unit_per_unit_discount_discount_type = [];
                if ($this->has('arr_product_unit_per_unit_discount_discount_type')) {
                    foreach ($this->arr_product_unit_per_unit_discount_discount_type as $parentIdx => $parentResult) {
                        foreach ($parentResult as $idx => $result) {
                            if ($result != '') {
                                $arr_product_unit_per_unit_discount_discount_type[$parentIdx][$idx] = $result;
                            } else {
                                $arr_product_unit_per_unit_discount_discount_type[$parentIdx][$idx] = -1;
                            }
                        }
                    }
                }
                $this->merge(['arr_product_unit_per_unit_discount_discount_type' => $arr_product_unit_per_unit_discount_discount_type]);

                $arr_product_unit_per_unit_discount_amount = [];
                if ($this->has('arr_product_unit_per_unit_discount_amount')) {
                    foreach ($this->arr_product_unit_per_unit_discount_amount as $parentIdx => $parentResult) {
                        foreach ($parentResult as $idx => $result) {
                            if ($result != '') {
                                $arr_product_unit_per_unit_discount_amount[$parentIdx][$idx] = $result;
                            } else {
                                $arr_product_unit_per_unit_discount_amount[$parentIdx][$idx] = 0;
                            }
                        }
                    }
                }
                $this->merge(['arr_product_unit_per_unit_discount_amount' => $arr_product_unit_per_unit_discount_amount]);

                $arr_product_unit_per_unit_sub_total_discount_id = [];
                if ($this->has('arr_product_unit_per_unit_sub_total_discount_id')) {
                    foreach ($this->arr_product_unit_per_unit_sub_total_discount_id as $parentIdx => $parentResult) {
                        foreach ($parentResult as $idx => $result) {
                            if ($result != '') {
                                $arr_product_unit_per_unit_sub_total_discount_id[$parentIdx][$idx] = Hashids::decode($result)[0];
                            } else {
                                $arr_product_unit_per_unit_sub_total_discount_id[$parentIdx][$idx] = null;
                            }
                        }
                    }
                }
                $this->merge(['arr_product_unit_per_unit_sub_total_discount_id' => $arr_product_unit_per_unit_sub_total_discount_id]);

                $arr_product_unit_per_unit_sub_total_discount_discount_type = [];
                if ($this->has('arr_product_unit_per_unit_sub_total_discount_discount_type')) {
                    foreach ($this->arr_product_unit_per_unit_sub_total_discount_discount_type as $parentIdx => $parentResult) {
                        foreach ($parentResult as $idx => $result) {
                            if ($result != '') {
                                $arr_product_unit_per_unit_sub_total_discount_discount_type[$parentIdx][$idx] = $result;
                            } else {
                                $arr_product_unit_per_unit_sub_total_discount_discount_type[$parentIdx][$idx] = -1;
                            }
                        }
                    }
                }
                $this->merge(['arr_product_unit_per_unit_sub_total_discount_discount_type' => $arr_product_unit_per_unit_sub_total_discount_discount_type]);

                $arr_product_unit_per_unit_sub_total_discount_amount = [];
                if ($this->has('arr_product_unit_per_unit_sub_total_discount_amount')) {
                    foreach ($this->arr_product_unit_per_unit_sub_total_discount_amount as $parentIdx => $parentResult) {
                        foreach ($parentResult as $idx => $result) {
                            if ($result != '') {
                                $arr_product_unit_per_unit_sub_total_discount_amount[$parentIdx][$idx] = $result;
                            } else {
                                $arr_product_unit_per_unit_sub_total_discount_amount[$parentIdx][$idx] = 0;
                            }
                        }
                    }
                }
                $this->merge(['arr_product_unit_per_unit_sub_total_discount_amount' => $arr_product_unit_per_unit_sub_total_discount_amount]);

                $arr_product_unit_vat_status = [];
                if ($this->has('arr_product_unit_vat_status')) {
                    for ($i = 0; $i < count($this->arr_product_unit_vat_status); $i++) {
                        if ($this->arr_product_unit_vat_status[$i] != '') {
                            array_push($arr_product_unit_vat_status, $this->arr_product_unit_vat_status[$i]);
                        } else {
                            array_push($arr_product_unit_vat_status, null);
                        }
                    }
                }
                $this->merge(['arr_product_unit_vat_status' => $arr_product_unit_vat_status]);

                $arr_product_unit_vat_rate = [];
                if ($this->has('arr_product_unit_vat_rate')) {
                    for ($i = 0; $i < count($this->arr_product_unit_vat_rate); $i++) {
                        if ($this->arr_product_unit_vat_rate[$i] != '') {
                            array_push($arr_product_unit_vat_rate, $this->arr_product_unit_vat_rate[$i]);
                        } else {
                            array_push($arr_product_unit_vat_rate, 0);
                        }
                    }
                }
                $this->merge(['arr_product_unit_vat_rate' => $arr_product_unit_vat_rate]);

                $arr_product_unit_vat_remarks = [];
                if ($this->has('arr_product_unit_vat_remarks')) {
                    for ($i = 0; $i < count($this->arr_product_unit_vat_remarks); $i++) {
                        if ($this->arr_product_unit_vat_remarks[$i] != '') {
                            array_push($arr_product_unit_vat_remarks, $this->arr_product_unit_vat_remarks[$i]);
                        } else {
                            array_push($arr_product_unit_vat_remarks, '');
                        }
                    }
                }
                $this->merge(['arr_product_unit_vat_remarks' => $arr_product_unit_vat_remarks]);

                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
