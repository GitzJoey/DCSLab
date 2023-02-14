<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    protected static $type;

	public static function make(...$parameters)
	{
		$resource = $parameters['resource'] ?? $parameters[0];
		self::$type = $parameters['type'] ?? $parameters[1] ?? null;
		return parent::make($resource);
	}

    public static function collection(...$parameters)
	{
		$resource = $parameters['resource'] ?? $parameters[0];
		self::$type = $parameters['type'] ?? $parameters[1] ?? null;
		return parent::collection($resource);
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
