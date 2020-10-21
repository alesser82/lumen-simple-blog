<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class JwtAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');

        if(!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Token not provided.'
            ], 401);
        }
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Provided token is expired.'
            ], 400);
        } catch(Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'An error while decoding token.'
            ], 400);
        }

        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $credentials->claims;
        return $next($request);
    }
}
