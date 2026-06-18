<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {

        try {

            // Ambil user dari JWT
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Owner akses semua
            if ($user->role === 'owner') {
                return $next($request);
            }

            // Cek role
            if (
                in_array(
                    strtolower($user->role),
                    array_map('strtolower', $roles)
                )
            ) {
                return $next($request);
            }

            return response()->json([
                'role_user' => $user->role,
                'required_role' => $roles
            ], 403);
            
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Token invalid'
            ], 401);
        }
    }
}
