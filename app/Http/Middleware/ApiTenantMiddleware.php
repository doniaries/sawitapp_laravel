<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantManager;
use Symfony\Component\HttpFoundation\Response;

class ApiTenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->perusahaan_id) {
            TenantManager::setTenant($user->perusahaan);
        }

        return $next($request);
    }
}
