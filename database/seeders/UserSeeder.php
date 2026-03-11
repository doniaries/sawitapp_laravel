<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'view_operasional',
            'create_operasional',
            'edit_operasional',
            'delete_operasional',
            'view_penjual',
            'create_penjual',
            'edit_penjual',
            'delete_penjual',
            'view_supir',
            'create_supir',
            'edit_supir',
            'delete_supir',
            'view_kendaraan',
            'create_kendaraan',
            'edit_kendaraan',
            'delete_kendaraan',
            'view_transaksi',
            'create_transaksi',
            'edit_transaksi',
            'delete_transaksi',
            'view_laporan',
            'create_laporan',
            'edit_laporan',
            'delete_laporan',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $superadmin = Role::create(['name' => 'super-admin']);
        $admin = Role::create(['name' => 'admin']);
        $kasir = Role::create(['name' => 'kasir']);

        // Give all permissions to superadmin
        $superadmin->givePermissionTo(Permission::all());

        // Create users
        $superadminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $superadminUser->assignRole('super-admin');

        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');

        if (!User::where('email', 'kasir1@gmail.com')->exists()) {
            $kasir = User::create([
                'name' => 'Taufik',
                'email' => 'kasir1@gmail.com', // Changed from kasir1@gmail.com
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $kasir->assignRole('kasir');
        }
    }
}
