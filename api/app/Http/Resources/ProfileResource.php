<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
        if ($this->type == 'UserProfile') {
            return [
                'full_name' => $this->createFullName($this->first_name, $this->last_name),
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'address' => $this->address,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'status' => $this->status->name,
                'tax_id' => $this->tax_id,
                'ic_num' => $this->ic_num,
                'img_path' => $this->img_path,
                'remarks' => $this->remarks,
            ];
        } else {
            return [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'address' => $this->address,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'status' => $this->status->name,
                'tax_id' => $this->tax_id,
                'ic_num' => $this->ic_num,
                'img_path' => $this->img_path,
                'remarks' => $this->remarks,
            ];
        }
    }

    private function createFullName(string $firstName, string $lastName)
    {
        if (empty($lastName)) {
            return $firstName;
        }

        return $firstName.' '.$lastName;
    }
}
