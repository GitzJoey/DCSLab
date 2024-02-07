<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderDiscountResource extends JsonResource
{
    public string $type = '';

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
            'company' => new CompanyResource($this->company),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->branch),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrder'), [
                'purchase_order' => new PurchaseOrderResource($this->purchaseOrder),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrderProductUnit' && $this->purchaseOrderProductUnit), [
                'purchase_order_product_unit' => new PurchaseOrderProductUnitResource($this->purchaseOrderProductUnit),
            ]),
            'order' => $this->order,
            'discount_type' => $this->discount_type,
            'amount' => $this->amount,
        ];
    }
}
