<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $user = Auth::user();
        
        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Your account is not active.',
            ])->onlyInput('email');
        }

        // Store coordinates
        $user->coordinates()->create([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        // Store the token in a cookie
        $cookie = cookie('jwt_token', $token, JWTAuth::factory()->getTTL() * 60, null, null, false, true);

        return redirect()->route('travel-history')->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);
            
            Auth::logout();
            $cookie = cookie()->forget('jwt_token');
            
            return redirect('/')->withCookie($cookie);
        } catch (\Exception $e) {
            return redirect('/')->withErrors(['error' => 'Failed to logout']);
        }
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => JWTAuth::refresh(),
                'type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ]);
    }

    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user()
        ]);
    }
} 