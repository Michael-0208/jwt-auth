<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtWebAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->cookie('jwt_token');
            
            if (!$token) {
                return redirect()->route('login');
            }

            $user = JWTAuth::setToken($token)->authenticate();
            
            if (!$user) {
                return redirect()->route('login');
            }

            auth()->login($user);

            return $next($request);
        } catch (TokenExpiredException $e) {
            return redirect()->route('login');
        } catch (TokenInvalidException $e) {
            return redirect()->route('login');
        } catch (JWTException $e) {
            return redirect()->route('login');
        }
    }
} 