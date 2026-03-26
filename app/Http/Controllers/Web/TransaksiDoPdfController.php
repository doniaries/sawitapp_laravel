<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TransaksiDo;
use Illuminate\Http\Request;

class TransaksiDoPdfController extends Controller
{
    public function __invoke($id)
    {
        $transaksi = TransaksiDo::findOrFail($id);
        return $transaksi->generatePdf();
    }
}
