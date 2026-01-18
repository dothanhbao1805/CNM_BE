<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Google_Client;


class AuthController extends Controller
{
    protected $userService;
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->userService->register($request->validated());

        return response()->json($result, 201);
    }

    public function verifyEmailOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->userService->verifyEmailOtp($data['email'], $data['otp']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->userService->login($request->validated());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 401);
        }

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'user' => $result['user'],
            'access_token' => $result['token'],
        ], 200);
    }

    public function logout(): JsonResponse
    {
        Log::info('Logout attempt');
        Log::info('Headers: ' . json_encode(request()->headers->all()));
        
        try {
            $token = JWTAuth::getToken();
            Log::info('Token: ' . $token);
            
            if (!$token) {
                Log::error('Token not found in request');
                return response()->json([
                    'success' => false,
                    'message' => 'Token không tồn tại',
                ], 401);
            }

            JWTAuth::invalidate($token);
            Log::info('Logout successful');

            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đăng xuất thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->userService->sendPasswordResetOtp($request->validated()['email']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function verifyResetPasswordOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->userService->verifyResetPasswordOtp($data['email'], $data['otp']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->userService->resetPassword($data['email'], $data['new_password']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function resendOtp(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->userService->resendOtp($request->validated()['email']);

        $statusCode = $result['success'] ? 200 : 
            (isset($result['code']) && $result['code'] === 'RATE_LIMIT' ? 429 : 400);

        return response()->json($result, $statusCode);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->userService->changePassword($data['email'], $data['password'], $data['new_password']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function refreshToken(): JsonResponse
    {
        $token = JWTAuth::getToken();
        $result = $this->userService->refreshToken($token);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 401);
        }

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'access_token' => $result['token'],
        ], 200);
    }
    public function socialLogin(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|in:google,facebook',
            'access_token' => 'required|string',
        ]);

        try {
            $provider = $request->provider;
            $accessToken = $request->access_token;
            
            if ($provider === 'google') {
                // Google trả về JWT credential (ID Token), cần verify bằng Google Client
                $client = new Google_Client(['client_id' => config('services.google.client_id')]);
                $payload = $client->verifyIdToken($accessToken);
                
                if (!$payload) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Token Google không hợp lệ',
                    ], 400);
                }
                
                // Tạo object user từ Google payload
                $socialUser = (object) [
                    'id' => $payload['sub'],
                    'email' => $payload['email'],
                    'name' => $payload['name'],
                    'avatar' => $payload['picture'] ?? null,
                ];
                
            } else if ($provider === 'facebook') {
                // Facebook trả về access token, có thể dùng userFromToken()
                try {
                    $fbUser = Socialite::driver('facebook')->userFromToken($accessToken);
                    
                    $socialUser = (object) [
                        'id' => $fbUser->getId(),
                        'email' => $fbUser->getEmail(),
                        'name' => $fbUser->getName(),
                        'avatar' => $fbUser->getAvatar(),
                    ];
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Token Facebook không hợp lệ',
                    ], 400);
                }
            }
            
            // Gọi service xử lý login
            $result = $this->userService->handleSocialLogin($provider, $socialUser);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'user' => $result['user'],
                'access_token' => $result['token'],
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Social login error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }
}
