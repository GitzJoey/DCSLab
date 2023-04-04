<?php

namespace App\Http\Resources;

use App\Enums\RecordStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
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
            'hId' => $this->hId,
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'parent_account' => $this->parent_id ? new ChartOfAccountResource($this->parentAccount) : null,
            'child_accounts' => ChartOfAccountResource::collection($this->whenLoaded('childAccounts')),
            'code' => $this->code,
            'name' => $this->name,
            'account_type' => $this->account_type->name,
            'can_have_child' => $this->can_have_child,
            'remarks' => $this->remarks,
            'status' => $this->setStatus($this->status, $this->deleted_at),
        ];
    }

    private function setStatus($status, $deleted_at)
    {
        if (! is_null($deleted_at)) {
            return RecordStatus::DELETED->name;
        } else {
            return $status->name;
        }
    }
}
