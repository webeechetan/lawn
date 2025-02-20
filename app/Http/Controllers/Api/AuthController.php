<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\VerifyOtpRequest;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request);

        if($result['success']) {
            
            return ApiResponse::success($result['data'], 'User registered successfully');
        }

        return ApiResponse::error($result['message'], 400);

    }

    public function login(LoginRequest $request)
    {
    
        $result = $this->authService->login($request);

        if($result['success']) {
            return ApiResponse::success($result['data'], 'User logged in successfully');
        }

        return ApiResponse::error($result['message'], 400);

    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $result = $this->authService->sendPasswordResetOTP($request);

        if($result['success']) {
            return ApiResponse::success([], 'OTP sent successfully');
        }

        return ApiResponse::error($result['message'], 400);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $result = $this->authService->verifyOtp($request);

        if($result['success']) {
            return ApiResponse::success([], 'OTP verified successfully');
        }

        return ApiResponse::error($result['message'], 400);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $result = $this->authService->resetPassword($request);

        if($result['success']) {
            return ApiResponse::success([], 'Password reset successfully');
        }

        return ApiResponse::error($result['message'], 400);
    }
}
