<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\User\OtpNotification;

class AuthService
{
    // Register user
    public function register($data)
    {
        try {
            $user = User::create([
                'email' => $data->email,
                'password' => Hash::make($data->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'success' => true,
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'User registration failed'
            ];
        }
    }

    // Login user
    public function login($data)
    {
        $res = Auth::attempt(['email' => $data->email, 'password' => $data->password]);

        if (!$res) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'token' => $token,
            ]
        ];

    }

    public function sendPasswordResetOTP($data)
    {
        
        $user = User::where('email', $data->email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $otp = mt_rand(1000, 9999);
        $user->otp = $otp;
        $user->otp_expire_at = now()->addMinutes(5);
        $user->save();

        $user->notify(new OtpNotification($user, $otp));

        return [
            'success' => true,
        ];
        
    }

    // Verify OTP
    public function verifyOtp($data)
    {
        $user = User::where('email', $data->email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        if ($user->otp != $data->otp || $user->otp_expire_at < now()) {
            return [
                'success' => false,
                'message' => 'Invalid OTP'
            ];
        }

        return [
            'success' => true,
            'data' => [
                'message' => 'OTP verified successfully',
            ]
        ];
    }

    // Reset password
    public function resetPassword($data){
        $user = User::where('email', $data->email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        if($user->otp != $data->otp || $user->otp_expire_at < now()){
            return [
                'success' => false,
                'message' => 'Invalid OTP'
            ];
        }

        $user->password = Hash::make($data->password);
        $user->otp = null;
        $user->otp_expire_at = null;
        $user->save();

        return [
            'success' => true,
            'data' => [
                'message' => 'Password reset successfully',
            ]
        ];


    }
}
