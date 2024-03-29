<?php

namespace App\Http\Resources;

use App\Enums\RecordStatus;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class EmployeeResource extends JsonResource
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
        $resource = [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->company),
            ]),
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->user),
            ]),
            $this->mergeWhen($this->relationLoaded('employeeAccesses'), [
                'employee_accesses' => EmployeeAccessResource::collection($this->employeeAccesses),
                'selected_companies' => $this->getSelectedCompanies($this->employeeAccesses ? $this->employeeAccesses : null),
                'selected_accesses' => $this->getSelectedAcsesses($this->employeeAccesses ? $this->employeeAccesses : null),
            ]),
            'code' => $this->code,
            'join_date' => $this->join_date,
            'status' => $this->setStatus($this->status, $this->deleted_at),
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

    private function setStatus($status, $deleted_at)
    {
        if (! is_null($deleted_at)) {
            return RecordStatus::DELETED->name;
        } else {
            return $status->name;
        }
    }
}
