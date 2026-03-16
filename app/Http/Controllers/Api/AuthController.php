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

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'perusahaan_id' => $user->perusahaan_id,
                'perusahaan_name' => $user->perusahaan?->name,
            ],
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
}
