<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
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
            'uuid' => $this->uuid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->whenLoaded('user')),
            ]),
            $this->mergeWhen($this->relationLoaded('employeeAccesses'), [
                'employee_accesses' => EmployeeAccessResource::collection($this->whenLoaded('employeeAccesses')),
                'selected_accesses' => $this->getSelectedAcsesses($this->whenLoaded('employeeAccesses') ? $this->employeeAccesses : null),
            ]),
            'code' => $this->code,
            'join_date' => $this->join_date,
            'status' => $this->status->name,
        ];
    }

    private function getSelectedAcsesses($employeeAccesses)
    {
        if (is_null($employeeAccesses)) return [];

        return $employeeAccesses->pluck('branch.hId');
    }
}