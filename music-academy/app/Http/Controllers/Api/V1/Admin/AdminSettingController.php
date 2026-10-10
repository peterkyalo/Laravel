<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    /**
     * Get all system settings.
     */
    public function index(): JsonResponse
    {
        $defaults = Setting::defaults();
        $dbSettings = Setting::pluck('value', 'key')->toArray();

        $settings = array_merge($defaults, $dbSettings);

        return response()->json([
            'settings' => $settings,
        ]);
    }

    /**
     * Update system settings.
     */
    public function update(Request $request): JsonResponse
    {
        $inputs = $request->except(['_token']);

        foreach ($inputs as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value]
            );
        }

        return response()->json([
            'message' => 'Settings updated successfully.',
        ]);
    }
}
