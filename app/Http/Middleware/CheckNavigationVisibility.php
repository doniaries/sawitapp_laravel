<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckNavigationVisibility
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, \Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        
        if ($user) {
            $tenantId = \Filament\Facades\Filament::getTenant()?->id;
            if ($tenantId) {
                setPermissionsTeamId($tenantId);
            }
            
            // Periksa apakah peran user diset untuk sembunyikan navigasi (is_visible = false)
            // Kita ambil peran pertama saja atau jika ada salah satu yang false
            $hasHiddenNav = $user->roles()
                ->where('roles.is_visible', false)
                ->exists();
            
            if ($hasHiddenNav) {
                \Filament\Support\Facades\FilamentView::registerRenderHook(
                    'panels::head.end',
                    fn (): \Illuminate\Contracts\View\View => view('filament.hooks.hide-sidebar'),
                );
            }
        }

        return $next($request);
    }
}
