<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Token extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "scopes" => $this->scopes,
            "revoked" => $this->revoked,
            "client" => [
                'id' => $this->client->uuid,
                'name' => $this->client->name,
                'logo_url' => isset($this->client->logo_url) ? $request->root() . $this->client->logo_url : null,
            ],
            'updated_at' => $this->updated_at,
        ];
    }
}
