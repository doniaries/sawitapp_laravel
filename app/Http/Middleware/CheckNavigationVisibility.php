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
                \Filament\Facades\Filament::registerRenderHook(
                    'panels::head.end',
                    fn () => new \Illuminate\Support\HtmlString('
                        <style>
                            .fi-sidebar { display: none !important; }
                            .fi-main { margin-inline-start: 0 !important; }
                            .fi-topbar-nav { display: none !important; }
                            /* Jika sidebar hilang, atur lebar konten agar tetap bagus */
                            .fi-layout > .flex-1 { margin-left: 0 !important; }
                        </style>
                    ')
                );
            }
        }

        return $next($request);
    }
}
