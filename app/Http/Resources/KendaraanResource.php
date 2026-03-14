<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KendaraanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_polisi' => $this->no_polisi,
            'supir_id' => $this->supir_id,
            'supir_nama' => $this->supir?->nama,
        ];
    }
}
