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
        $tab = $request->query('tab', 'hari_ini');

        // Logic based on tab if dates are not specific
        if ($request->has('tab') && !$request->has('start_date')) {
            if ($tab === 'bulan_ini') {
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
            } elseif ($tab === 'tahun_ini') {
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
            }
        }

        $viewData = $service->generatePdfReport($startDate, $endDate);
        $pdf = Pdf::loadView('laporan.keuangan-harian', $viewData);
        $pdf->setPaper('a4', 'landscape');

        $filename = "laporan-keuangan-$startDate-ke-$endDate.pdf";
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
