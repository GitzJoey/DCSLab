<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderDiscountResource extends JsonResource
{
    protected string $type = '';

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
        if ($this->type == 'PurchaseOrder') {
            return [
                'id' => Hashids::encode($this->id),
                'ulid' => $this->ulid,
                'company' => new CompanyResource($this->company),
                $this->mergeWhen($this->relationLoaded('branch'), [
                    'branch' => new BranchResource($this->whenLoaded('branch')),
                ]),
                $this->mergeWhen($this->relationLoaded('purchaseOrder'), [
                    'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
                ]),
                'discount_type' => $this->discount_type,
                'amount' => $this->amount,
            ];
        } elseif ($this->type == 'PurchaseOrderProductUnit') {
            return [
                'id' => Hashids::encode($this->id),
                'ulid' => $this->ulid,
                'company' => new CompanyResource($this->company),
                $this->mergeWhen($this->relationLoaded('branch'), [
                    'branch' => new BranchResource($this->whenLoaded('branch')),
                ]),
                $this->mergeWhen($this->relationLoaded('purchaseOrder'), [
                    'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
                ]),
                $this->mergeWhen($this->relationLoaded('purchaseOrderProductUnit'), [
                    'purchase_order_product_unit' => new PurchaseOrderProductUnitResource($this->whenLoaded('purchaseOrderProductUnit')),
                ]),
                'discount_type' => $this->discount_type,
                'amount' => $this->amount,
            ];
        }
    }
}
