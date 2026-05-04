<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Perusahaan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FilamentAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $perusahaan;

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan ada perusahaan untuk tenant
        $this->perusahaan = Perusahaan::first() ?? Perusahaan::create([
            'name' => 'CV SUCCESS MANDIRI', 
            'slug' => 'cv-success-mandiri',
            'saldo' => 0
        ]);

        // Ambil user super admin pertama atau buat jika tidak ada
        $this->admin = User::where('email', 'superadmin@gmail.com')->first() ?? User::first() ?? User::factory()->create();
        
        // Kaitkan user ke perusahaan jika perlu
        if (!$this->admin->perusahaans()->wherePivot('perusahaan_id', $this->perusahaan->id)->exists()) {
            $this->admin->perusahaans()->attach($this->perusahaan);
        }
    }

    protected function getUrl($path = '')
    {
        return "/admin/{$this->perusahaan->slug}{$path}";
    }

    /** @test */
    public function can_access_dashboard()
    {
        $this->actingAs($this->admin)
            ->get($this->getUrl())
            ->assertSuccessful();
    }

    /** @test */
    public function can_access_transaksi_do_list()
    {
        $this->actingAs($this->admin)
            ->get($this->getUrl('/transaksi-dos'))
            ->assertSuccessful();
    }

    /** @test */
    public function can_access_jurnal_keuangan_list()
    {
        $this->actingAs($this->admin)
            ->get($this->getUrl('/jurnal-keuangans'))
            ->assertSuccessful();
    }

    /** @test */
    public function can_access_tambah_saldo_list()
    {
        $this->actingAs($this->admin)
            ->get($this->getUrl('/tambah-saldo'))
            ->assertSuccessful();
    }

    /** @test */
    public function can_access_transaksi_operasional_list()
    {
        $this->actingAs($this->admin)
            ->get($this->getUrl('/transaksi-operasionals'))
            ->assertSuccessful();
    }

    /** @test */
    public function can_access_tutup_hari_list()
    {
        $this->actingAs($this->admin)
            ->get($this->getUrl('/tutup-haris'))
            ->assertSuccessful();
    }

    /** @test */
    public function can_access_penjual_list()
    {
        $this->actingAs($this->admin)
            ->get($this->getUrl('/penjuals'))
            ->assertSuccessful();
    }

    /** @test */
    public function can_access_supir_list()
    {
        $this->actingAs($this->admin)
            ->get($this->getUrl('/supirs'))
            ->assertSuccessful();
    }

    /** @test */
    public function can_access_pekerja_list()
    {
        $this->actingAs($this->admin)
            ->get($this->getUrl('/pekerjas'))
            ->assertSuccessful();
    }
}
