<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\TransaksiDos\Pages\CreateTransaksiDo;
use App\Filament\Resources\TransaksiDos\Pages\ListTransaksiDos;
use App\Filament\Resources\TransaksiDos\TransaksiDoResource;
use App\Models\Penjual;
use App\Models\Supir;
use App\Models\TransaksiDo;
use App\Models\User;
use App\Models\Perusahaan;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransaksiDoResourceTest extends TestCase
{
    use RefreshDatabase;

    protected $perusahaan;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->perusahaan = Perusahaan::factory()->create([
            'saldo' => 1000000000, // Give 1 Billion saldo for testing
        ]);
        $this->user = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'perusahaan_id' => null,
            'is_active' => true,
        ]);

        $this->user->perusahaans()->attach($this->perusahaan);

        setPermissionsTeamId($this->perusahaan->id);
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $this->actingAs($this->user);
        Filament::setTenant($this->perusahaan);
    }

    /** @test */
    public function can_render_list_page()
    {
        $this->get(TransaksiDoResource::getUrl('index', ['tenant' => $this->perusahaan]))
            ->assertSuccessful();
    }

    /** @test */
    public function can_create_transaction_with_empty_numeric_fields()
    {
        $penjual = Penjual::factory()->create(['perusahaan_id' => $this->perusahaan->id]);
        $supir = Supir::factory()->create(['perusahaan_id' => $this->perusahaan->id]);

        Livewire::test(CreateTransaksiDo::class)
            ->fillForm([
                'tanggal' => now()->format('Y-m-d H:i:s'),
                'penjual_id' => $penjual->id,
                'supir_id' => $supir->id,
                'tonase' => 1000,
                'harga_satuan' => 3500,
                'cara_bayar' => 'tunai',
                'upah_bongkar' => '', // Empty, should become 0
                'biaya_lain' => null,  // Null, should become 0
                'pembayaran_hutang' => '', // Empty, should become 0
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transaksi_do', [
            'penjual_id' => $penjual->id,
            'tonase' => 1000,
            'harga_satuan' => 3500,
            'sub_total' => 3500000,
            'upah_bongkar' => 0,
            'biaya_lain' => 0,
            'pembayaran_hutang' => 0,
            'sisa_bayar' => 3500000,
        ]);
    }

    /** @test */
    public function calculations_are_correct_when_fields_are_filled()
    {
        $penjual = Penjual::factory()->create(['perusahaan_id' => $this->perusahaan->id]);
        $supir = Supir::factory()->create(['perusahaan_id' => $this->perusahaan->id]);

        Livewire::test(CreateTransaksiDo::class)
            ->fillForm([
                'tanggal' => now()->format('Y-m-d H:i:s'),
                'penjual_id' => $penjual->id,
                'supir_id' => $supir->id,
                'tonase' => 1000,
                'harga_satuan' => 3500,
                'upah_bongkar' => 100000,
                'biaya_lain' => 50000,
                'cara_bayar' => 'tunai',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transaksi_do', [
            'sub_total' => 3500000,
            'upah_bongkar' => 100000,
            'biaya_lain' => 50000,
            'sisa_bayar' => 3350000,
        ]);
    }

    /** @test */
    public function debt_payment_updates_seller_debt()
    {
        $penjual = Penjual::factory()->create([
            'perusahaan_id' => $this->perusahaan->id,
            'hutang' => 1000000,
        ]);
        $supir = Supir::factory()->create(['perusahaan_id' => $this->perusahaan->id]);

        Livewire::test(CreateTransaksiDo::class)
            ->fillForm([
                'tanggal' => now()->format('Y-m-d H:i:s'),
                'penjual_id' => $penjual->id,
                'supir_id' => $supir->id,
                'tonase' => 1000,
                'harga_satuan' => 3500,
                'pembayaran_hutang' => 200000,
                'cara_bayar' => 'tunai',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transaksi_do', [
            'penjual_id' => $penjual->id,
            'pembayaran_hutang' => 200000,
            'sisa_bayar' => 3300000,
        ]);

        $penjual->refresh();
        $this->assertEquals(800000, $penjual->sisa_hutang);
    }

    /** @test */
    public function cannot_create_transaction_if_saldo_is_insufficient()
    {
        $this->perusahaan->update(['saldo' => 100000]); // Low saldo

        $penjual = Penjual::factory()->create(['perusahaan_id' => $this->perusahaan->id]);
        $supir = Supir::factory()->create(['perusahaan_id' => $this->perusahaan->id]);

        Livewire::test(CreateTransaksiDo::class)
            ->fillForm([
                'tanggal' => now()->format('Y-m-d H:i:s'),
                'penjual_id' => $penjual->id,
                'supir_id' => $supir->id,
                'tonase' => 1000,
                'harga_satuan' => 3500,
                'sisa_bayar' => 3500000, // Manually set to trigger validation
                'cara_bayar' => 'tunai',
            ])
            ->call('create')
            ->assertHasFormErrors(['cara_bayar']);
    }

    /** @test */
    public function can_create_transaction_with_transfer_even_if_saldo_is_zero()
    {
        $this->perusahaan->update(['saldo' => 0]);

        $penjual = Penjual::factory()->create(['perusahaan_id' => $this->perusahaan->id]);
        $supir = Supir::factory()->create(['perusahaan_id' => $this->perusahaan->id]);

        Livewire::test(CreateTransaksiDo::class)
            ->fillForm([
                'tanggal' => now()->format('Y-m-d H:i:s'),
                'penjual_id' => $penjual->id,
                'supir_id' => $supir->id,
                'tonase' => 1000,
                'harga_satuan' => 3500,
                'sisa_bayar' => 3500000,
                'cara_bayar' => 'transfer',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transaksi_do', [
            'cara_bayar' => 'transfer',
            'sisa_bayar' => 3500000,
        ]);
    }
    /** @test */
    public function can_update_transaction_and_recalculate()
    {
        $penjual = Penjual::factory()->create(['perusahaan_id' => $this->perusahaan->id]);
        $supir = Supir::factory()->create(['perusahaan_id' => $this->perusahaan->id]);
        
        $transaksi = TransaksiDo::factory()->create([
            'perusahaan_id' => $this->perusahaan->id,
            'penjual_id' => $penjual->id,
            'supir_id' => $supir->id,
            'tonase' => 1000,
            'harga_satuan' => 3000, // 3M
            'upah_bongkar' => 100000,
            'sisa_bayar' => 2900000,
        ]);

        Livewire::test(\App\Filament\Resources\TransaksiDos\Pages\EditTransaksiDo::class, [
            'record' => $transaksi->getRouteKey(),
        ])
            ->fillForm([
                'tonase' => 2000, // 2000 * 3000 = 6M
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transaksi_do', [
            'id' => $transaksi->id,
            'tonase' => 2000,
            'sub_total' => 6000000,
            'sisa_bayar' => 5900000, // 6M - 100k
        ]);
    }
}
