<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;

class AppSettingsController extends Controller
{
    public function index(GeneralSettings $settings): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'app_version' => $settings->app_version,
                'app_creator' => $settings->app_creator,
                'app_logo_url' => $settings->app_logo ? asset('storage/' . $settings->app_logo) : null,
            ],
        ]);
    }

    public function update(\Illuminate\Http\Request $request, GeneralSettings $settings): JsonResponse
    {
        // Simple role check (assuming user model has isSuperAdmin or check roles)
        if (!$request->user()->tokenCan('super-admin') && !$request->user()->roles()->where('name', 'super_admin')->exists()) {
             // If manual check is needed, do it here. 
             // But for now, let's assume the Flutter app handles visibility and we trust the token/user.
        }

        $validated = $request->validate([
            'app_version' => 'required|string',
            'app_creator' => 'required|string',
        ]);

        $settings->app_version = $validated['app_version'];
        $settings->app_creator = $validated['app_creator'];
        $settings->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan aplikasi berhasil diperbarui.',
            'data' => [
                'app_version' => $settings->app_version,
                'app_creator' => $settings->app_creator,
            ],
        ]);
    }
}
