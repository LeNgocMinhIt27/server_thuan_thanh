<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Profile extends JsonResource
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
        $isLoadInfo = $this->resource->relationLoaded('info');
        $array = [
            'id' => $this->id,
            'avatar_url' => !empty($this->avatar_url) ? config('app.url') . $this->avatar_url : '',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'username' => $this->username,
            'inactive' => $this->inactive,
            'role_code' => $this->role_code,
            'roles' => $this->roles,
            'is_giao_vien' => $this->is_giao_vien,
            'is_sinh_vien' => $this->is_sinh_vien,
            $this->mergeWhen($isLoadInfo, [
                'info' => $this->info,
            ])
        ];
        return $array;
    }
}
