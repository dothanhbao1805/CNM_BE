<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Enums\UserRole;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Verify JWT token trước
            $user = JWTAuth::parseToken()->authenticate();
            
            // Kiểm tra user có tồn tại không
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Kiểm tra có phải admin không
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. Admin access required.'
                ], 403);
            }

        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn'
            ], 401);

        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ'
            ], 401);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token không được tìm thấy'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực: ' . $e->getMessage()
            ], 500);
        }

        return $next($request);
    }
}