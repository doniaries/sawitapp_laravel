<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransaksiDoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomor' => $this->nomor,
            'tanggal' => $this->tanggal->format('Y-m-d H:i:s'),
            'penjual' => new PenjualResource($this->whenLoaded('penjual')),
            'supir_nama' => $this->supir?->nama,
            'kendaraan_plat' => $this->kendaraan?->no_polisi,
            'tonase' => (float) $this->tonase,
            'harga_satuan' => (float) $this->harga_satuan,
            'sub_total' => (float) $this->sub_total,
            'upah_bongkar' => (float) $this->upah_bongkar,
            'biaya_lain' => (float) $this->biaya_lain,
            'sisa_hutang_penjual' => (float) $this->sisa_hutang_penjual,
            'sisa_bayar' => (float) $this->sisa_bayar,
            'cara_bayar' => $this->cara_bayar,
        ];
    }
}
