<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\User\OtpNotification;
use App\Helpers\ApiResponse;

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
                'user' => $user,
                'token' => $token,
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
        $user = User::where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        return $this->sendOtp($user);

    }

    // Send OTP
    public function sendOtp($user)
    {
        $otp = mt_rand(1000, 9999);

        try {
            $user->notify(new OtpNotification($user, $otp));
            $user->otp = $otp;
            $user->otp_expire_at = now()->addMinutes(5);
            $user->save();

            return [
                'success' => true,
                'message' => 'OTP sent successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send OTP'
            ];
        }
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

        // Clear OTP after successful verification
        $user->otp = null;
        $user->otp_expire_at = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'user' => $user,
            'token' => $token,
        ];
    }
}
