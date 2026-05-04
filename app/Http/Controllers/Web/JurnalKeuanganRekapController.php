<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\JurnalKeuanganService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class JurnalKeuanganRekapController extends Controller
{
    public function __invoke(Request $request, JurnalKeuanganService $service)
    {
        // Authenticate manually if token is provided in query (for external browser)
        if (!Auth::check() && $request->has('token')) {
            $token = PersonalAccessToken::findToken($request->token);
            if ($token && $token->tokenable) {
                Auth::login($token->tokenable);
            }
        }

        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $startDate = $request->query('start_date', now()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $reportType = $request->query('report_type', 'semua');
        $rentang = $request->query('rentang', 'hari_ini');

        // Determine Title
        $judulSuffix = match($rentang) {
            'hari_ini' => "HARIAN",
            'bulan_ini' => "BULANAN",
            default => "PERIODE",
        };

        $judulBase = match($reportType) {
            'do' => "LAPORAN TRANSAKSI DO",
            'operasional' => "LAPORAN OPERASIONAL",
            default => "LAPORAN KEUANGAN",
        };

        $judul = "$judulBase $judulSuffix";

        $viewData = $service->generatePdfReport($startDate, $endDate, $reportType);
        $viewData['judul'] = $judul;
        $viewData['reportType'] = $reportType;

        $pdf = Pdf::loadView('laporan.keuangan-harian', $viewData);
        $pdf->setPaper('a4', 'landscape');

        $filename = \Illuminate\Support\Str::slug($judul) . "-$startDate-ke-$endDate.pdf";
        $disposition = $request->has('download') ? 'attachment' : 'inline';

        return response()->stream(
            fn() => print($pdf->output()),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "$disposition; filename=\"$filename\"",
            ]
        );
    }
}
