<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Supirs\Pages\ListSupirs;
use App\Filament\Resources\Supirs\SupirResource;
use App\Models\Supir;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupirResourceTest extends TestCase
{
    use RefreshDatabase;

    protected $perusahaan;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->perusahaan = \App\Models\Perusahaan::factory()->create();
        $this->user = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'perusahaan_id' => null,
            'is_active' => true,
        ]);

        // Hubungkan user ke perusahaan (pivot table perusahaan_user)
        $this->user->perusahaans()->attach($this->perusahaan);

        // Berikan role super_admin agar bisa akses resource (Shield)
        // Fitur teams aktif, jadi harus set team id dulu
        setPermissionsTeamId($this->perusahaan->id);
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_render_list_page()
    {
        $this->get(SupirResource::getUrl('index', ['tenant' => $this->perusahaan]))
            ->assertSuccessful();
    }

    /** @test */
    public function can_list_supirs()
    {
        $supirs = Supir::factory()->count(5)->create([
            'perusahaan_id' => $this->perusahaan->id,
        ]);

        Filament::setTenant($this->perusahaan);

        Livewire::test(ListSupirs::class)
            ->assertCanSeeTableRecords($supirs);
    }

    /** @test */
    public function can_render_create_page()
    {
        $this->get(SupirResource::getUrl('create', ['tenant' => $this->perusahaan]))
            ->assertSuccessful();
    }

    /** @test */
    public function can_render_edit_page()
    {
        $supir = Supir::factory()->create([
            'perusahaan_id' => $this->perusahaan->id,
        ]);

        $this->get(SupirResource::getUrl('edit', [
            'record' => $supir,
            'tenant' => $this->perusahaan,
        ]))
            ->assertSuccessful();
    }
}
