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
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company'))
            ]),
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->whenLoaded('user'))
            ]),
            $this->mergeWhen($this->relationLoaded('employeeAccesses'), [
                'employee_accesses' => EmployeeAccessResource::collection($this->whenLoaded('employeeAccesses')),
                'selected_companies' => $this->getSelectedCompany($this->whenLoaded('employeeAccesses') ? $this->employee_accesses : null),
                'selected_accesses' => $this->getSelectedAcsesses($this->whenLoaded('employeeAccesses') ? $this->employee_accesses : null)
            ]),
            'code' => $this->code,
            'join_date' => $this->join_date,
            'status' => $this->status->name
        ];
    }

    private function getSelectedCompany($employeeAccesses)
    {
        if (is_null($employeeAccesses)) return [];

        return $employeeAccesses->count() != 0 ? $employeeAccesses->pluck('branch.company.hId') : [];
    }

    private function getSelectedAcsesses($employeeAccesses)
    {
        if (is_null($employeeAccesses)) return [];

        return $employeeAccesses->pluck('branch.hId');
    }
}