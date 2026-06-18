<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {

            // kalau ada token → authenticate
            if ($token = JWTAuth::getToken()) {

                $user = JWTAuth::parseToken()->authenticate();

                if (!$user) {
                    return response()->json([
                        'message' => 'Unauthorized'
                    ], 401);
                }

                auth('api')->setUser($user);
            }

        } catch (JWTException $e) {

            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
