<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ForgotPasswordNotification;
use App\Notifications\RegisterationNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
         
        // Generate JWT token
        $token = JWTAuth::fromUser($user);
        
        if($token){
            Log::info('User registered successfully: ' . $user->email);
            $user->notify(new RegisterationNotification($user));
            return response()->json([
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'user' => $user
            ], 201);
        }else{
            Log::channel('critical_errors')->error('Failed to generate token for user: ' . $user->email);
            return response()->json(['error' => 'Unable to Register'], 500);
        }
        
    }
    
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }   

        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            Log::channel('critical_errors')->info('Unauthorized login attempt: ' . $request->email);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(compact('token'));
        
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        try {
            $user->notify(new ForgotPasswordNotification($user));
            Log::info('Password reset link sent to user: ' . $user->email);
            return response()->json(['message' => 'Password reset link sent to your email']);
        } catch (\Exception $e) {
            Log::channel('critical_errors')->error('Failed to send password reset notification: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to send password reset notification'], 500);
        }

        
    }

    public function resetPassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }   

        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        if($user->save()){

            Log::info('Password reset successfully for user: ' . $user->email);
            $user->notify(new ResetPasswordNotification($user));
            return response()->json(['message' => 'Password reset successfully']);
        }else{
            Log::channel('critical_errors')->error('Failed to reset password for user: ' . $user->email);
            return response()->json(['error' => 'Unable to reset password'], 500);
        }

    }
        
    
}
