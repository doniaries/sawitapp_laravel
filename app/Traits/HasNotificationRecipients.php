<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait HasNotificationRecipients
{
    /**
     * Get the users who should receive notifications for a given company.
     * Includes all super_admins and admins of the specific company.
     */
    protected function getNotificationRecipients(?int $perusahaanId = null): Collection
    {
        // Get all super_admins
        $superAdmins = User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->get();

        // If no perusahaanId, just return superAdmins
        if (!$perusahaanId) {
            return $superAdmins->unique('id');
        }

        // Get admins of the specific company
        $companyAdmins = User::where('perusahaan_id', $perusahaanId)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

        return $superAdmins->merge($companyAdmins)->unique('id')->filter(fn ($user) => $user->is_active);
    }
}
