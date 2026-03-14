<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = [
            "ViewAny:Role", "View:Role", "Create:Role", "Update:Role", "Delete:Role", "DeleteAny:Role",
            "ViewAny:User", "View:User", "Create:User", "Update:User", "Delete:User", "Restore:User", "RestoreAny:User", "ForceDelete:User", "ForceDeleteAny:User",
            "ViewAny:Penjual", "View:Penjual", "Create:Penjual", "Update:Penjual", "Delete:Penjual", "Restore:Penjual", "RestoreAny:Penjual", "ForceDelete:Penjual", "ForceDeleteAny:Penjual",
            "ViewAny:Perusahaan", "View:Perusahaan", "Create:Perusahaan", "Update:Perusahaan", "Delete:Perusahaan", "Restore:Perusahaan", "RestoreAny:Perusahaan", "ForceDelete:Perusahaan", "ForceDeleteAny:Perusahaan",
            "ViewAny:Supir", "View:Supir", "Create:Supir", "Update:Supir", "Delete:Supir", "Restore:Supir", "RestoreAny:Supir", "ForceDelete:Supir", "ForceDeleteAny:Supir",
            "ViewAny:Kendaraan", "View:Kendaraan", "Create:Kendaraan", "Update:Kendaraan", "Delete:Kendaraan", "Restore:Kendaraan", "RestoreAny:Kendaraan", "ForceDelete:Kendaraan", "ForceDeleteAny:Kendaraan",
            "ViewAny:TransaksiOperasional", "View:TransaksiOperasional", "Create:TransaksiOperasional", "Update:TransaksiOperasional", "Delete:TransaksiOperasional", "Restore:TransaksiOperasional", "RestoreAny:TransaksiOperasional", "ForceDelete:TransaksiOperasional", "ForceDeleteAny:TransaksiOperasional",
            "ViewAny:TransaksiDo", "View:TransaksiDo", "Create:TransaksiDo", "Update:TransaksiDo", "Delete:TransaksiDo", "Restore:TransaksiDo", "RestoreAny:TransaksiDo", "ForceDelete:TransaksiDo", "ForceDeleteAny:TransaksiDo", "Replicate:TransaksiDo", "Reorder:TransaksiDo",
            "ViewAny:JurnalKeuangan", "View:JurnalKeuangan", "Create:JurnalKeuangan", "Update:JurnalKeuangan", "Delete:JurnalKeuangan", "Restore:JurnalKeuangan", "RestoreAny:JurnalKeuangan", "ForceDelete:JurnalKeuangan", "ForceDeleteAny:JurnalKeuangan",
            "ViewAny:Pekerja", "View:Pekerja", "Create:Pekerja", "Update:Pekerja", "Delete:Pekerja", "Restore:Pekerja", "RestoreAny:Pekerja", "ForceDelete:Pekerja", "ForceDeleteAny:Pekerja",
            "ViewAny:Pabrik", "View:Pabrik", "Create:Pabrik", "Update:Pabrik", "Delete:Pabrik", "Restore:Pabrik", "RestoreAny:Pabrik", "ForceDelete:Pabrik", "ForceDeleteAny:Pabrik",
            "ViewAny:RiwayatPembayaranHutang", "View:RiwayatPembayaranHutang", "Create:RiwayatPembayaranHutang", "Update:RiwayatPembayaranHutang", "Delete:RiwayatPembayaranHutang", "Restore:RiwayatPembayaranHutang", "RestoreAny:RiwayatPembayaranHutang", "ForceDelete:RiwayatPembayaranHutang", "ForceDeleteAny:RiwayatPembayaranHutang",
            "Page:Dashboard", "Page:Tenancy\EditPerusahaanProfile", "Page:Tenancy\RegisterPerusahaan"
        ];

        // Admin: Semua kecuali Role management
        $adminPermissions = collect($allPermissions)->filter(function ($permission) {
            return !str_contains($permission, ':Role');
        })->toArray();

        // Kasir: Terbatas pada Transaksi DO, Operasional, Laporan, dan Master Data (hanya Lihat)
        $kasirPermissions = [
            // Dashboard
            "Page:Dashboard",

            // Transaksi DO
            "ViewAny:TransaksiDo", "View:TransaksiDo", "Create:TransaksiDo", "Update:TransaksiDo",
            
            // Transaksi Operasional
            "ViewAny:TransaksiOperasional", "View:TransaksiOperasional", "Create:TransaksiOperasional", "Update:TransaksiOperasional",

            // Jurnal Keuangan
            "ViewAny:JurnalKeuangan", "View:JurnalKeuangan",

            // Master Data (Hanya View/List)
            "ViewAny:Penjual", "View:Penjual",
            "ViewAny:Supir", "View:Supir",
            "ViewAny:Kendaraan", "View:Kendaraan",
            "ViewAny:Pekerja", "View:Pekerja",
            "ViewAny:Perusahaan", "View:Perusahaan"
        ];

        $rolesWithPermissions = [
            [
                'name' => 'super_admin',
                'guard_name' => 'web',
                'permissions' => $allPermissions
            ],
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'permissions' => $adminPermissions
            ],
            [
                'name' => 'kasir',
                'guard_name' => 'web',
                'permissions' => $kasirPermissions
            ],
        ];

        $rolesWithPermissions = json_encode($rolesWithPermissions);

        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeder Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (!blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = Utils::getRoleModel()::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (!blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) =>
                            Utils::getPermissionModel()::firstOrCreate([
                                'name' => $permission,
                                'guard_name' => $rolePlusPermission['guard_name'],
                            ])
                        );
                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    protected static function makeDirectPermissions(string $directPermissions): void
    {
        if (!blank($permissions = json_decode($directPermissions, true))) {
            foreach ($permissions as $permission) {
                Utils::getPermissionModel()::firstOrCreate([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
