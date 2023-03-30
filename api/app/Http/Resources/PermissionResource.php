<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
        return [
            $this->mergeWhen(env('APP_DEBUG', false), [
                'id' => $this->id,
            ]),
            'displayName' => $this->display_name,
        ];
    }
}
