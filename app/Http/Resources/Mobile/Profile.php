<?php

namespace App\Http\Resources\Mobile;

use App\Http\Resources\Role;
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
        $isLoadRole = $this->resource->relationLoaded('roles');
        $isLoadGroup = $this->resource->relationLoaded('group');
        $isLoadDonVi = $this->resource->relationLoaded('donVi');
        $array = [
            'id' => $this->uuid,
            'avatar_url' => !empty($this->avatar_url) ? config('app.url') . $this->avatar_url : '',
            'birthday' => $this->birthday,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'username' => $this->username,
            'name' => $this->name,
            $this->mergeWhen($isLoadRole, [
                'role' => $this->roles && count($this->roles) > 0 ? new Role($this->roles[0]) : [],
                'roles' => Role::collection($this->roles),
                'role_id' => $this->roles && count($this->roles) > 0 ? $this->roles[0]->getKey() : null,
            ]),
        ];
        if ($isLoadDonVi && !empty($this->donVi)) {
            $array['don_vi_name'] = $this->donVi->ten;
        }
        if ($isLoadGroup && !empty($this->group)) {
            $array['group_name'] = $this->group->name;
        }
        return $array;
    }
}
