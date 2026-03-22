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
            ],
        ]);
    }
}
