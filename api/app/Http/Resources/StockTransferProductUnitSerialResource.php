<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class StockTransferProductUnitSerialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'stock_transfer' => new StockTransferResource($this->stock_transfer),
            'stock_trasnfer_product_unit' => new StockTransferProductUnitResource($this->stock_trasnfer_produc_unit),
            'serial' => $this->serial,
        ];
    }
}
