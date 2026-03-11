<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AssignSuperAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'superadmin@gmail.com')->first();

        if ($user) {
            $user->syncRoles(['super_admin']);
            $this->command->info('Role super_admin assigned to: ' . $user->email);
        } else {
            $this->command->warn('User superadmin@gmail.com not found!');
        }
    }
}
