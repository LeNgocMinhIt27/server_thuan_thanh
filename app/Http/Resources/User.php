<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    private $option = [];
    public function __construct($resource, $option = [])
    {
        $this->resource = $resource;
        $this->option = $option;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $array = [
            'id' => $this->id,
            'avatar_url' => !empty($this->avatar_url) ? config('app.url') . $this->avatar_url : '',
            'birthday' => $this->birthday,
            'mobile' => $this->mobile,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // 'military_id' => $this->military_id,
            // 'military' => $this->whenLoaded('military'),
            // 'province_id' => $this->province_id,
            // 'province' => $this->whenLoaded('province'),
            'username' => $this->username,
            'inactive' => $this->inactive,
        ];
        if (isset($this->login_logs_count)) {
            $array['login_logs_count'] = $this->login_logs_count;
        }
        return $array;
    }
}
