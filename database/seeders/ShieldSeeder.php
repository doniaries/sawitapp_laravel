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

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_user","view_any_user","create_user","update_user","delete_user","restore_user","restore_any_user","force_delete_user","force_delete_any_user","delete_any_user","view_penjual","view_any_penjual","create_penjual","update_penjual","delete_penjual","restore_penjual","restore_any_penjual","force_delete_penjual","force_delete_any_penjual","delete_any_penjual","view_perusahaan","view_any_perusahaan","create_perusahaan","update_perusahaan","delete_perusahaan","restore_perusahaan","restore_any_perusahaan","force_delete_perusahaan","force_delete_any_perusahaan","delete_any_perusahaan","view_supir","view_any_supir","create_supir","update_supir","delete_supir","restore_supir","restore_any_supir","force_delete_supir","force_delete_any_supir","delete_any_supir","view_kendaraan","view_any_kendaraan","create_kendaraan","update_kendaraan","delete_kendaraan","restore_kendaraan","restore_any_kendaraan","force_delete_kendaraan","force_delete_any_kendaraan","delete_any_kendaraan","view_operasional","view_any_operasional","create_operasional","update_operasional","delete_operasional","restore_operasional","restore_any_operasional","force_delete_operasional","force_delete_any_operasional","delete_any_operasional","view_transaksi_do","view_any_transaksi_do","create_transaksi_do","update_transaksi_do","delete_transaksi_do","restore_transaksi_do","restore_any_transaksi_do","force_delete_transaksi_do","force_delete_any_transaksi_do","delete_any_transaksi_do","view_laporan_keuangan","view_any_laporan_keuangan","create_laporan_keuangan","update_laporan_keuangan","delete_laporan_keuangan","restore_laporan_keuangan","restore_any_laporan_keuangan","force_delete_laporan_keuangan","force_delete_any_laporan_keuangan","delete_any_laporan_keuangan","view_pekerja","view_any_pekerja","create_pekerja","update_pekerja","delete_pekerja","restore_pekerja","restore_any_pekerja","force_delete_pekerja","force_delete_any_pekerja","delete_any_pekerja","page_Dashboard"]},{"name":"admin","guard_name":"web","permissions":["page_Dashboard"]},{"name":"kasir","guard_name":"web","permissions":["page_Dashboard"]}]';

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
