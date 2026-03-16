<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    /**
     * Test jika halaman preview 404 berfungsi.
     */
    public function test_preview_404_page_loads_correctly()
    {
        $response = $this->get('/preview-404');

        $response->assertStatus(200);
        $response->assertViewIs('errors.404');
        $response->assertSee('404');
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('default-logo.png');
    }

    /**
     * Test jika halaman preview 500 berfungsi.
     */
    public function test_preview_500_page_loads_correctly()
    {
        $response = $this->get('/preview-500');

        $response->assertStatus(200);
        $response->assertViewIs('errors.500');
        $response->assertSee('500');
        $response->assertSee('Kesalahan Server');
    }

    /**
     * Test jika akses URL sembarang memicu halaman 404 kustom.
     * (Catatan: Ini akan berhasil jika APP_DEBUG=false)
     */
    public function test_non_existent_url_shows_custom_404()
    {
        // Kita paksa environment menjadi production untuk ngetes error page asli
        config(['app.debug' => false]);

        $response = $this->get('/halaman-yang-pasti-tidak-ada-' . uniqid());

        $response->assertStatus(404);
        $response->assertSee('404'); 
        $response->assertSee('Halaman Tidak Ditemukan');
    }
}
