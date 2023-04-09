<?php

namespace App\Http\Resources;

use App\Enums\RecordStatus;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderProductUnitResource extends JsonResource
{
    protected string $type;

    public function type(string $value)
    {
        $this->type = $value;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->whenLoaded('branch')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrderProductUnits'), [
                'product_unit' => new ProductUnitResource($this->whenLoaded('purchaseOrderProductUnits')),
            ]),
            $this->mergeWhen($this->relationLoaded('product'), [
                'product' => new ProductResource($this->whenLoaded('product')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrder'), [
                'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            ]),
            'qty' => $this->qty,
            'product_unit_amount_per_unit' => $this->product_unit_amount_per_unit,
            'product_unit_amount_total' => $this->product_unit_amount_total,
            'product_unit_initial_price' => $this->product_unit_initial_price,            
            $this->mergeWhen($this->relationLoaded('productUnitPerUnitDiscount'), [
                'product_unit_per_unit_discount' => (new PurchaseOrderDiscountResource($this->whenLoaded('productUnitPerUnitDiscount')))->type('PurchaseOrderProductUnit'),
            ]),
            'product_unit_sub_total' => $this->product_unit_sub_total,
            $this->mergeWhen($this->relationLoaded('productUnitPerUnitSubTotalDiscount'), [
                'product_unit_per_unit_sub_total_discount' => (new PurchaseOrderDiscountResource($this->whenLoaded('productUnitPerUnitSubTotalDiscount')))->type('PurchaseOrderProductUnit'),
            ]),
            'product_unit_total' => $this->product_unit_total,
            'product_unit_global_discount_percent' => $this->product_unit_global_discount_percent,
            'product_unit_global_discount_nominal' => $this->product_unit_global_discount_nominal,
            'product_unit_final_price' => $this->product_unit_final_price,
            'vat_status' => $this->setVatStatus($this->vat_status, $this->deleted_at),
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'remarks' => $this->remarks,
        ];
    }

    private function setVatStatus($vat_status, $deleted_at)
    {
        if (! is_null($deleted_at)) {
            return RecordStatus::DELETED->name;
        } else {
            return $vat_status->name;
        }
    }
}
