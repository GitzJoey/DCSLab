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
        $resource = [
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
                'selected_companies' => $this->getSelectedCompanies($this->whenLoaded('employeeAccesses') ? $this->employeeAccesses : null),
                'selected_accesses' => $this->getSelectedAcsesses($this->whenLoaded('employeeAccesses') ? $this->employeeAccesses : null),
            ]),
            'code' => $this->code,
            'join_date' => $this->join_date,
            'status' => $this->status->name,
        ];

        return $resource;
    }

    private function getSelectedCompanies($employeeAccesses)
    {
        if (is_null($employeeAccesses)) {
            return [];
        }

        $companyIds = [];
        for ($i = 0; $i < $employeeAccesses->count(); $i++) {
            $companyId = $employeeAccesses[0]->branch->company->hId;
            array_push($companyIds, $companyId);
        }
        $companyIds = collect($companyIds)->unique();

        return $companyIds;
    }

    private function getSelectedAcsesses($employeeAccesses)
    {
        if (is_null($employeeAccesses)) {
            return [];
        }

        $branchIds = $employeeAccesses->pluck('branch.hId');

        return $branchIds;
    }
}