<?php

namespace App\Models;


use App\Models\Penjual;
use App\Models\Supir;
use App\Models\Perusahaan;
use App\Traits\DokumentasiTrait;
use App\Traits\GenerateMonthlyNumber;
use App\Traits\JurnalKeuanganTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use Illuminate\Support\Facades\{DB, Log, Cache};
use SimpleSoftwareIO\QrCode\Facades\QrCode;
// use BaconQrCode\Renderer\ImageRenderer;
// use BaconQrCode\Renderer\Image\SvgImageBackEnd;
// use BaconQrCode\Renderer\RendererStyle\RendererStyle;
// use BaconQrCode\Writer;

class TransaksiDo extends Model
{
    use HasFactory, SoftDeletes, JurnalKeuanganTrait, DokumentasiTrait, GenerateMonthlyNumber, BelongsToTenant;
    
    protected static function booted()
    {
        static::creating(function ($transaksi) {
            if ($transaksi->no_polisi) {
                $transaksi->no_polisi = mb_strtoupper($transaksi->no_polisi);
            }
        });

        static::updating(function ($transaksi) {
            if ($transaksi->no_polisi) {
                $transaksi->no_polisi = mb_strtoupper($transaksi->no_polisi);
            }
        });
    }

    protected $table = 'transaksi_do';
    protected $with = ['penjual', 'supir', 'kendaraan']; // Default eager loading


    protected $fillable = [
        'id',
        'client_uuid',
        'client_created_at',
        'client_updated_at',
        'synced_at',
        'perusahaan_id',
        'user_id',
        'nomor',
        'tanggal',
        'penjual_id',
        'supir_id',
        'no_polisi',
        'tonase',
        'harga_satuan',
        'sub_total',
        'upah_bongkar',
        'biaya_lain',
        'keterangan_biaya_lain',
        'hutang_awal',          // Updated
        'pembayaran_hutang',    // Updated
        'sisa_hutang_penjual',  // Updated
        'cara_bayar',
        'nominal_tunai',
        'sisa_bayar',
        'bukti_transfer',
        'keterangan_pembayaran',
        'is_mismatch',
        'bukti_rekap',
        // 'file_do',
        // 'status_bayar',
        // 'catatan',
    ];


    // MASIH DIPAKAI - Update casting untuk kolom baru
    protected $casts = [
        'tanggal' => 'datetime',
        'client_created_at' => 'datetime',
        'client_updated_at' => 'datetime',
        'synced_at' => 'datetime',
        'tonase' => 'decimal:0',
        'harga_satuan' => 'decimal:0',
        'sub_total' => 'decimal:0',
        'upah_bongkar' => 'integer',
        'biaya_lain' => 'integer',
        'hutang_awal' => 'decimal:0',         // Updated
        'pembayaran_hutang' => 'decimal:0',   // Updated
        'sisa_hutang_penjual' => 'decimal:0', // Updated
        'nominal_tunai' => 'decimal:0',
        'sisa_bayar' => 'decimal:0',
        'is_mismatch' => 'boolean',
        // 'status_bayar' => 'string',
    ];

    protected $attributes = [
        'sub_total' => 0,
        'upah_bongkar' => 0,
        'biaya_lain' => 0,
        'hutang_awal' => 0,           // Updated
        'pembayaran_hutang' => 0,     // Updated
        'sisa_hutang_penjual' => 0,   // Updated
        'nominal_tunai' => 0,
        'sisa_bayar' => 0,
        // 'status_bayar' => 'Belum Lunas',
    ];

    const CARA_BAYAR = [
        'tunai' => 'tunai',
        'transfer' => 'transfer',
        'tunai & transfer' => 'tunai & transfer',
        'cair di luar' => 'cair di luar',
        'belum dibayar' => 'belum dibayar',
    ];



    public function penjual(): BelongsTo
    {
        return $this->belongsTo(Penjual::class)->withDefault([
            'nama' => 'Tidak Diketahui'
        ]);
    }

    // Tambahkan relation ke laporan keuangan
    public function jurnalKeuangan()
    {
        return $this->hasMany(JurnalKeuangan::class, 'referensi_id')
            ->where('sumber_transaksi', 'DO');
    }

    public function operasional()
    {
        return $this->hasMany(TransaksiOperasional::class, 'pihak_id')
            ->where('pihak_type', self::class);
    }

    // Accessor untuk hutang penjual
    public function getHutangPenjualAttribute(): int
    {
        return $this->penjual ? $this->penjual->hutang : 0;
    }



    //cetak pdf di transaksi DO
    public function generatePdf()
    {
        try {
            $perusahaan = \App\Models\Perusahaan::query()->first(['*']);
            
            if (!$perusahaan) {
                throw new \Exception('Data Perusahaan belum dikonfigurasi. Silakan isi data perusahaan terlebih dahulu.');
            }

            $qrcode = $this->generateQrCode();

            $pdf = PDF::loadView('pdf.transaksi-do', [
                'transaksi' => $this,
                'perusahaan' => $perusahaan,
                'qrcode' => $qrcode
            ]);

            $pdf->setPaper('F4', 'portrait');

            return $pdf->stream("DO-{$this->nomor}.pdf");
        } catch (\Exception $e) {
            Log::error('Error generating PDF:', [
                'error' => $e->getMessage(),
                'transaksi' => $this->toArray()
            ]);
            throw $e;
        }
    }

    public function supir(): BelongsTo
    {
        return $this->belongsTo(Supir::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'no_polisi', 'no_polisi')->withDefault([
            'nama' => 'Tanpa Armada'
        ]);
    }



    public function getTotalAttribute()
    {
        return $this->sub_total;
    }

    public function scopeWithTotals(Builder $query): Builder
    {
        return $query->select('*')
            ->addSelect(DB::raw('sub_total as total_amount'));  // Use sub_total consistently
    }

    public function generateQrCode()
    {
        return base64_encode(
            QrCode::format('svg')
                ->size(100)
                ->errorCorrection('H')
                ->generate(json_encode([
                    'no_do' => $this->nomor,
                    'tonase' => $this->tonase,
                    'tanggal' => $this->tanggal->format('d/m/Y H:i'),
                    'penjual' => $this->penjual ? $this->penjual->nama : 'N/A',
                    'supir' => $this->supir ? $this->supir->nama : 'N/A',
                    'no_polisi' => $this->no_polisi ?? 'N/A',
                ]))
        );
    }

    public function scopeCurrentMonth(Builder $query): Builder
    {
        return $query->whereMonth('tanggal', '=', now()->month, 'and')
            ->whereYear('tanggal', '=', now()->year, 'and');
    }

    public function scopeByPenjual(Builder $query, ?string $penjualId): Builder
    {
        return $query->where('penjual_id', '=', $penjualId, 'and');
    }

    public function getCachedAttribute(string $key)
    {
        return Cache::remember("transaksi_do_{$this->id}_{$key}", 3600, function () use ($key) {
            return $this->getAttribute($key);
        });
    }

    /**
     * Logika Perhitungan Bisnis
     */
    public static function calculateSubTotal(float $tonase, float $hargaSatuan): float
    {
        return (float) round($tonase * $hargaSatuan);
    }

    public static function calculateSisaBayar(float $subTotal, float $upahBongkar, float $biayaLain, float $bayarHutang): float
    {
        $sisa = $subTotal - ($upahBongkar + $biayaLain + $bayarHutang);
        return (float) round($sisa);
    }

    public static function calculateSisaHutang(float $hutangAwal, float $bayarHutang): float
    {
        $sisa = $hutangAwal - $bayarHutang;
        return (float) max(0, round($sisa));
    }

    /**
     * Update semua perhitungan berdasarkan data input form
     * Digunakan oleh Filament afterStateUpdated
     */
    public static function updateCalculations(array $data): array
    {
        $tonase = \App\Traits\HasCurrencyInput::sanitizeNumber($data['tonase'] ?? 0);
        $hargaSatuan = \App\Traits\HasCurrencyInput::sanitizeNumber($data['harga_satuan'] ?? 0);
        
        $subTotal = self::calculateSubTotal($tonase, $hargaSatuan);
        
        $upahBongkar = \App\Traits\HasCurrencyInput::sanitizeNumber($data['upah_bongkar'] ?? 0);
        $biayaLain = \App\Traits\HasCurrencyInput::sanitizeNumber($data['biaya_lain'] ?? 0);
        $bayarHutang = \App\Traits\HasCurrencyInput::sanitizeNumber($data['pembayaran_hutang'] ?? 0);
        $hutangAwal = \App\Traits\HasCurrencyInput::sanitizeNumber($data['hutang_awal'] ?? 0);
        
        $sisaBayar = self::calculateSisaBayar($subTotal, $upahBongkar, $biayaLain, $bayarHutang);
        $sisaHutang = self::calculateSisaHutang($hutangAwal, $bayarHutang);

        return [
            'sub_total' => $subTotal,
            'sisa_bayar' => $sisaBayar,
            'sisa_hutang_penjual' => $sisaHutang,
        ];
    }

    /**
     * Logika Validasi Bisnis
     */
    public static function validatePotonganHutang($value, $hutangAwal, $subTotal, $upahBongkar, $biayaLain): ?string
    {
        $val = (float) \App\Traits\HasCurrencyInput::sanitizeNumber($value);
        $hutang = (float) \App\Traits\HasCurrencyInput::sanitizeNumber($hutangAwal);
        
        // 1. Cek terhadap sisa hutang penjual
        if ($val > $hutang) {
            return "Potongan tidak boleh melebihi sisa hutang penjual (" . number_format($hutang, 0, ',', '.') . ")";
        }

        // 2. Cek terhadap sisa hasil transaksi (Sub Total - Potongan Biaya)
        $totalBiaya = (float) \App\Traits\HasCurrencyInput::sanitizeNumber($upahBongkar) + (float) \App\Traits\HasCurrencyInput::sanitizeNumber($biayaLain);
        $maxPotongHutang = (float) \App\Traits\HasCurrencyInput::sanitizeNumber($subTotal) - $totalBiaya;

        if ($val > $maxPotongHutang) {
            $limit = max(0, $maxPotongHutang);
            return "Potongan tidak boleh melebihi sisa hasil transaksi (" . number_format($limit, 0, ',', '.') . ")";
        }

        return null;
    }

    public static function validateCaraBayar($value, $sisaBayar, $nominalTunai, $perusahaanSaldo, $user): ?string
    {
        // Jika Admin/SuperAdmin/Pimpinan, boleh lanjut meskipun saldo tidak cukup
        if ($user && method_exists($user, 'isAdminOrSuperAdmin') && $user->isAdminOrSuperAdmin()) {
            return null;
        }

        $cekNominal = 0;
        if ($value === 'tunai') {
            $cekNominal = \App\Traits\HasCurrencyInput::sanitizeNumber($sisaBayar);
        } elseif ($value === 'tunai & transfer') {
            $cekNominal = \App\Traits\HasCurrencyInput::sanitizeNumber($nominalTunai);
        }

        if ($cekNominal > 0 && $cekNominal > $perusahaanSaldo) {
            return "Saldo perusahaan tidak mencukupi (Saldo: " . money($perusahaanSaldo, 'IDR') . "). Hanya Admin yang dapat melanjutkan transaksi ini.";
        }

        return null;
    }

    public static function validateNominalTunai($value, $sisaBayar): ?string
    {
        $totalBayar = \App\Traits\HasCurrencyInput::sanitizeNumber($sisaBayar);
        $val = \App\Traits\HasCurrencyInput::sanitizeNumber($value);
        
        if ($val > $totalBayar) {
            return "Nominal tunai tidak boleh melebihi total bayar (" . money($totalBayar, 'IDR') . ")";
        }

        return null;
    }
}
