<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OtpService
{
    protected $otpLength;
    protected $expiryMinutes;
    protected $maxAttempts;
    
    public function __construct()
    {
        $this->otpLength = config('otp.length', 6);
        $this->expiryMinutes = config('otp.expiry_minutes', 5);
        $this->maxAttempts = config('otp.max_attempts', 3);
    }

    /**
     * Tạo OTP mới cho email
     */
    public function generate(string $email): string
    {
        // Tạo OTP ngẫu nhiên
        $otp = str_pad(
            random_int(0, pow(10, $this->otpLength) - 1), 
            $this->otpLength, 
            '0', 
            STR_PAD_LEFT
        );

        // Lưu vào cache với TTL
        $key = $this->getOtpKey($email);
        Cache::put($key, [
            'otp' => $otp,
            'attempts' => 0,
            'is_verified' => false,
            'created_at' => Carbon::now()->toDateTimeString()
        ], now()->addMinutes($this->expiryMinutes));

        return $otp;
    }

    /**
     * Xác thực OTP
     */
    public function verify(string $email, string $otp): bool
    {
        $key = $this->getOtpKey($email);
        $data = Cache::get($key);

        if (!$data) {
            return false; // OTP không tồn tại hoặc đã hết hạn
        }

        // Kiểm tra số lần thử
        if ($data['attempts'] >= $this->maxAttempts) {
            Cache::forget($key);
            return false;
        }

        // Tăng số lần thử
        $data['attempts']++;
        
        // Kiểm tra OTP
        if ($data['otp'] === $otp) {
            // OTP đúng → Đánh dấu đã verified
            $data['is_verified'] = true;
            Cache::put($key, $data, now()->addMinutes($this->expiryMinutes));
            return true;
        }

        // OTP sai → Lưu lại số lần thử
        Cache::put($key, $data, now()->addMinutes($this->expiryMinutes));
        return false;
    }

    /**
     * Kiểm tra OTP có tồn tại không
     */
    public function exists(string $email): bool
    {
        return Cache::has($this->getOtpKey($email));
    }

    /**
     * Kiểm tra OTP đã được verified chưa
     */
    public function isVerified(string $email): bool
    {
        $data = Cache::get($this->getOtpKey($email));
        return $data && ($data['is_verified'] ?? false);
    }

    /**
     * Xóa OTP
     */
    public function delete(string $email): void
    {
        Cache::forget($this->getOtpKey($email));
    }

    /**
     * Lấy số lần thử còn lại
     */
    public function getRemainingAttempts(string $email): int
    {
        $data = Cache::get($this->getOtpKey($email));
        
        if (!$data) {
            return $this->maxAttempts;
        }

        return max(0, $this->maxAttempts - $data['attempts']);
    }

    /**
     * Kiểm tra có thể resend không
     */
    public function canResend(string $email): bool
    {
        $key = $this->getResendKey($email);
        return !Cache::has($key);
    }

    /**
     * Set rate limit cho resend
     */
    public function setResendLimit(string $email): void
    {
        $key = $this->getResendKey($email);
        $cooldown = config('otp.resend_cooldown', 60);
        Cache::put($key, true, now()->addSeconds($cooldown));
    }

    /**
     * Key cho OTP trong cache
     */
    protected function getOtpKey(string $email): string
    {
        return 'otp:' . md5(strtolower($email));
    }
    
    /**
     * Key cho resend rate limit
     */
    protected function getResendKey(string $email): string
    {
        return 'otp:resend:' . md5(strtolower($email));
    }
}