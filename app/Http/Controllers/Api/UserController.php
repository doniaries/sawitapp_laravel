<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna (untuk Admin/Superadmin).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        // Hanya superadmin atau admin yang bisa melihat daftar pengguna
        if (! $user->isAdminOrSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $query = User::query();

        // Jika bukan superadmin, hanya tampilkan pengguna di perusahaan yang sama
        if ($user->email !== 'superadmin@gmail.com') {
            $query->where('perusahaan_id', $user->perusahaan_id);
        }

        $users = $query->with(['perusahaan', 'roles'])
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return UserResource::collection($users);
    }
}
