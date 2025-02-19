<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\VerifyOtpRequest;

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
            return ApiResponse::success($result, 'User registered successfully');
        }

        return ApiResponse::error($result['message'], 400);

    }

    public function login(LoginRequest $request)
    {
    
        $user = User::where('email', $request->email)->first();

        $result = $this->authService->sendOtp($user);

        if($result['success']) {
            return ApiResponse::success($result);
        }

        return ApiResponse::error($result['message'], 400);

    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $result = $this->authService->verifyOtp($request);

        if($result['success']) {
            return ApiResponse::success($result, 'OTP verified successfully');
        }

        return ApiResponse::error($result['message'], 400);
    }
}
