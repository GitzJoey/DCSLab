<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch_id' => new BranchResource($this->branch),
            'code' => $this->code,
            'date' => $this->date,
            'due_days' => $this->due_days,
            'warehouse_id' => new WarehouseResource($this->warehouse),
            'supplier_id' => new SupplierResource($this->supplier),
            'purchase_order_id' => new PurchaseOrderResource($this->purchaseOrder),
            'delivery_note_reference' => $this->delivery_note_reference,
            'purchase_tax_invoice_number' => $this->purchase_tax_invoice_number,
            'purchase_tax_invoice_vat_base' => $this->purchase_tax_invoice_vat_base,
            'purchase_tax_invoice_vat' => $this->purchase_tax_invoice_vat,
            'return_tax_invoice_number' => $this->return_tax_invoice_number,
            'return_tax_invoice_vat_base' => $this->return_tax_invoice_vat_base,
            'return_tax_invoice_vat' => $this->return_tax_invoice_vat,
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'purchase_total' => $this->purchase_total,
            'purchase_global_discount_rate' => $this->purchase_global_discount_rate,
            'purchase_global_discount_fixed' => $this->purchase_global_discount_fixed,
            'purchase_additional_cost' => $this->purchase_additional_cost,
            'purchase_rounding' => $this->purchase_rounding,
            'purchase_grand_total' => $this->purchase_grand_total,
            'return_total' => $this->return_total,
            'return_global_discount_rate' => $this->return_global_discount_rate,
            'return_global_discount_fixed' => $this->return_global_discount_fixed,
            'return_rounding' => $this->return_rounding,
            'return_grand_total' => $this->return_grand_total,
            'amount_due' => $this->amount_due,
            'amount_paid_by_purchase_order_down_payment' => $this->amount_paid_by_purchase_order_down_payment,
            'amount_paid_by_purchase_return' => $this->amount_paid_by_purchase_return,
            'amount_paid_before_invoice' => $this->amount_paid_before_invoice,
            'amount_paid_on_invoice' => $this->amount_paid_on_invoice,
            'amount_paid_after_invoice' => $this->amount_paid_after_invoice,
            'amount_paid_total' => $this->amount_paid_total,
            'amount_due' => $this->amount_due,
            'is_paid_off' => $this->is_paid_off,
            'is_valid' => $this->is_valid,
        ];
    }
}
