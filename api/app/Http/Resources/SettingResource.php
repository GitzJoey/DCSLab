<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
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
            'theme' => $this->getSettings('PREFS.THEME', $this),
            'date_format' => $this->getSettings('PREFS.DATE_FORMAT', $this),
            'time_format' => $this->getSettings('PREFS.TIME_FORMAT', $this),
        ];
    }

    private function getSettings($searchKey, $settings)
    {
        if (is_null($settings)) {
            return '';
        }

        $result = $settings->firstWhere('key', $searchKey);

        return $result ? $result->value : '';
    }
}
