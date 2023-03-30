<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    protected string $type;

    public function type(string $value) {
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
        if ($this->type == 'UserProfile') {
            return [
                'fullName' => $this->first_name.' '.$this->last_name, 
                'country' => $this->country,
                'status' => $this->status->name,
                'imgPath' => $this->img_path,
            ];
        } else {
            return [
                'firstName' => $this->first_name,
                'lastName' => $this->last_name,
                'address' => $this->address,
                'city' => $this->city,
                'postalCode' => $this->postal_code,
                'country' => $this->country,
                'status' => $this->status->name,
                'taxId' => $this->tax_id,
                'icNum' => $this->ic_num,
                'imgPath' => $this->img_path,
                'remarks' => $this->remarks,
            ];
        }
    }
}
