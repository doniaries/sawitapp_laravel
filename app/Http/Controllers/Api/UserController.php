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

        $users = $query->with(['perusahaan', 'roles', 'perusahaans'])
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return UserResource::collection($users);
    }

    /**
     * Ganti password pengguna yang sedang login.
     */
    public function changePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
            'status' => 'success'
        ]);
    }

    /**
     * Reset password pengguna lain (khusus Superadmin).
     */
    public function resetPassword(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->email !== 'superadmin@gmail.com') {
            abort(403, 'Hanya Superadmin yang dapat melakukan reset password.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return response()->json([
            'message' => "Password pengguna {$user->name} berhasil direset.",
            'status' => 'success'
        ]);
    }
}
