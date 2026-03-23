<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'perusahaan_id' => $this->perusahaan_id,
            'perusahaan_name' => $this->perusahaan?->name,
            'is_active' => $this->is_active,
            'photo' => $this->photo,
            'photo_url' => $this->photo ? asset('storage/' . $this->photo) : null,
            'perusahaan_logo_url' => $this->perusahaan?->logo,
            'roles' => $this->roles->pluck('name'),
        ];
    }
}
