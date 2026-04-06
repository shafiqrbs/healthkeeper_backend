<?php

namespace Modules\HoribaIntegration\App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\HoribaIntegration\App\Models\HoribaDeviceModel;
use Symfony\Component\HttpFoundation\Response;

class HoribaBridgeAuthMiddleware
{
    /**
     * Authenticate Bridge Agent requests via Bearer token + optional IP whitelist.
     * Token must match an active device's api_token in horiba_devices table.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing Bearer token',
            ], 401);
        }

        $device = HoribaDeviceModel::where('api_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$device) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid or inactive device token',
            ], 403);
        }

        // IP whitelist check — if bridge_ip is set, enforce it
        if ($device->bridge_ip) {
            $clientIp = $request->ip();
            $allowedIps = array_map('trim', explode(',', $device->bridge_ip));

            if (!in_array($clientIp, $allowedIps)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Unauthorized IP: ' . $clientIp,
                ], 403);
            }
        }

        // Attach device to request for controller use
        $request->merge(['_horiba_device' => $device]);

        return $next($request);
    }
}
