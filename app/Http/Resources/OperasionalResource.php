<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperasionalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal->format('Y-m-d H:i:s'),
            'operasional' => $this->operasional,
            'kategori' => $this->kategori?->value,
            'kategori_label' => $this->kategori_label,
            'tipe_nama' => $this->tipe_nama,
            'nama_terkait' => $this->nama,
            'nominal' => (float) $this->nominal,
            'keterangan' => $this->keterangan,
        ];
    }
}
