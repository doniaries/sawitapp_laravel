<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Tenancy\EditTeamProfile;
use App\Models\Perusahaan;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Carbon\Carbon;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
// use EightCedars\FilamentInactivityGuard\FilamentInactivityGuardPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;


class AdminPanelProvider extends PanelProvider
{
    /**
     * Konfigurasi widget yang aktif di Dashboard
     */
    public static array $dashboardWidgets = [
        'stats' => true,
        'daily_chart' => false,
        'monthly_chart' => false,
        'top_hutang' => false,
        'top_tonase' => false,
    ];

    public function panel(Panel $panel): Panel
    {

        return $panel
            ->default()
            // ->spa()
            // ->topNavigation()
            ->maxContentWidth('full')
            ->id('admin')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->path('admin')
            ->favicon(asset('images/success.png'))
            ->login()
            ->font('Poppins')
            ->colors([
                'primary' => Color::Amber,
                'secondary' => Color::Cyan,
                'danger' => Color::Red,
                'warning' => Color::Yellow,
                'success' => Color::Green,
                'info' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \Filament\Widgets\AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->authMiddleware([
                Authenticate::class
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\CheckNavigationVisibility::class,
            ])
            ->tenantMiddleware([
                \App\Http\Middleware\SetPermissionsTeamId::class,
            ], isPersistent: true)
            ->databaseNotifications()
            // ->searchableTenantMenu()
            ->tenant(Perusahaan::class, slugAttribute: 'slug', ownershipRelationship: 'perusahaan')
            ->tenantProfile(EditTeamProfile::class)
            ->navigationGroups([
                'Transaksi',
                'Data Master',
                'Laporan',
                'Pengaturan',
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    ->scopeToTenant(false),
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->environment('local'))
                    ->users([
                        'Admin' => 'superadmin@gmail.com',
                        'Pimpinan' => 'yondra@gmail.com',
                        'Kasir' => 'kasir1@gmail.com',
                    ]),
                \Rupadana\ApiService\ApiServicePlugin::make(),
                ...(class_exists('EightCedars\FilamentInactivityGuard\FilamentInactivityGuardPlugin') ? [
                    \EightCedars\FilamentInactivityGuard\FilamentInactivityGuardPlugin::make()
                        ->inactiveAfter(600) // 10 menit
                        ->showNoticeFor(60)  // 1 menit
                        ->keepActiveOn(['change', 'select', 'mousemove'], mergeWithDefaults: true),
                ] : []),
            ]);
    }
}
