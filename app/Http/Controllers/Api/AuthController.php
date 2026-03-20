<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun Anda tidak aktif. Silakan hubungi admin.'
            ], 403);
        }

        // Auto select perusahaan untuk admin/superadmin jika kosong
        if ($user->isAdminOrSuperAdmin() && is_null($user->perusahaan_id)) {
            $firstPerusahaan = \App\Models\Perusahaan::first();
            if ($firstPerusahaan) {
                $user->update(['perusahaan_id' => $firstPerusahaan->id]);
            }
        }

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function getPerusahaans(Request $request)
    {
        $user = $request->user();
        if ($user->email === 'superadmin@gmail.com') {
            $perusahaans = \App\Models\Perusahaan::all();
        } else {
            $perusahaans = $user->perusahaans;
        }

        return response()->json($perusahaans);
    }

    public function switchPerusahaan(Request $request)
    {
        $request->validate([
            'perusahaan_id' => 'required|exists:perusahaan,id',
        ]);

        $user = $request->user();
        
        // Cek akses
        if ($user->email !== 'superadmin@gmail.com') {
            if (!$user->perusahaans()->where('perusahaan_id', $request->perusahaan_id)->exists()) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke perusahaan ini.'], 403);
            }
        }

        $user->update(['perusahaan_id' => $request->perusahaan_id]);

        return response()->json([
            'message' => 'Berhasil pindah perusahaan.',
            'perusahaan_id' => $user->perusahaan_id
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout.'
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
            }

            $path = $request->file('photo')->store('profile_photos', 'public');
            $user->update(['photo' => $path]);

            return response()->json([
                'message' => 'Foto profil berhasil diperbarui.',
                'photo_url' => asset('storage/' . $path),
                'user' => new UserResource($user)
            ]);
        }

        return response()->json(['message' => 'Gagal mengunggah foto.'], 400);
    }
}
