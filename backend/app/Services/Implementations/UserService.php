<?php
namespace App\Services\Implementations;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use App\Notifications\UserCreatedNotification;
use App\Notifications\VerifyEmailOtpNotification;
use App\Services\Interfaces\UserServiceInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\CloudinaryService;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserService implements UserServiceInterface
{
    protected $userRepository;
    protected $otpService;
    protected $cloudinaryService;

    public function __construct(UserRepositoryInterface $userRepository, OtpService $otpService, CloudinaryService $cloudinaryService)
    {
        $this->userRepository = $userRepository;
        $this->otpService = $otpService;
        $this->cloudinaryService = $cloudinaryService;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAll();
    }

    public function getUserById(string $id)
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(array $data)
    {   
        $randomPassword = $this->createRandomPassword(8);
        $data['password'] = Hash::make($randomPassword);
        $data['status'] = UserStatus::ACTIVE;
        $data['role'] = $data['role'] ?? UserRole::USER;
        $data['is_verified'] = true;
        $data['email_verified_at'] = now();
        if (array_key_exists('gender', $data)) {
            $data['gender'] = is_null($data['gender']) ? null : (int)$data['gender'];
        }

        $user = $this->userRepository->create($data);

        $user->notify(new UserCreatedNotification($randomPassword));

        return $user;
    }

    public function updateUser(User $user, array $data)
    {
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existing = $this->userRepository->findByEmail($data['email']);
            if ($existing && $existing->id !== $user->id) {
                throw new \Exception('Email đã tồn tại');
            }
        }
        if (array_key_exists('gender', $data)) {
            $data['gender'] = is_null($data['gender']) ? null : (int)$data['gender'];
        }
        
        if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
            try {
                // Xóa avatar cũ nếu có
                if ($user->avatar) {
                    $oldPublicId = $this->cloudinaryService->extractPublicId($user->avatar);
                    if ($oldPublicId) {
                        $this->cloudinaryService->deleteImage($oldPublicId);
                    }
                }

                // Upload avatar mới
                $avatarUrl = $this->cloudinaryService->uploadImage(
                    $data['avatar'], 
                    'user_avatar'
                );
                
                $data['avatar'] = $avatarUrl;
                
            } catch (\Exception $e) {
                throw new \Exception('Upload avatar thất bại: ' . $e->getMessage());
            }
            } else {
                // Nếu không upload avatar mới, giữ nguyên avatar cũ
                unset($data['avatar']);
            }
        return $this->userRepository->update($user->id, $data);
    }
    public function deleteUser(User $user)
    {
        return $this->userRepository->delete($user->id);
    }

    public function register(array $data)
    {
        $data['is_verified'] = false;
        $data['role'] = UserRole::USER;
        $data['status'] = UserStatus::ACTIVE;
        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->create($data);

        $this->sendVerifyEmailOtp($user->email);

        return [
            'success' => true,
            'message' => 'Đăng ký thành công! Vui lòng kiểm tra email để xác minh.',
            'email'   => $user->email,
        ];
    }

    public function login(array $credentials)
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return [
                'success' => false,
                'message' => 'Password or email is incorrect',
            ];
        }

        // Sau đó mới kiểm tra trạng thái
        if($user->status === UserStatus::INACTIVE) {
            return [
                'success' => false,
                'message' => 'Your account has been disabled. Please contact support.',
            ];
        }

        if(!$user->is_verified) {
            return [
                'success' => false,
                'message' => 'Your email is not verified. Please verify your email before logging in.',
            ];
        }

        // Tạo token
        $token = JWTAuth::fromUser($user);

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
        ];
    }

    public function sendPasswordResetOtp(string $email): array
    {
        if(!$this->userRepository->emailExists($email)) {
            return [
                'success' => false,
                'message' => 'Email không tồn tại trong hệ thống',
            ];
        }

        if (!$this->otpService->canResend($email)) {
            return [
                'success' => false,
                'message' => 'Vui lòng đợi 1 phút trước khi gửi lại OTP',
                'code' => 'RATE_LIMIT'
            ];
        }

        $user = $this->userRepository->findByEmail($email);
        $otp = $this->otpService->generate($email);
        $this->otpService->setResendLimit($email);

        $user->notify(new SendOtpNotification($otp));

        return [
            'success' => true,
            'message' => 'Mã OTP đã được gửi đến email của bạn',
            'data' => [
                'expires_in_minutes' => config('otp.expiry_minutes'),
                'max_attempts' => config('otp.max_attempts')
            ]
        ];
    }

    public function sendVerifyEmailOtp(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email không tồn tại'
            ];
        }

        if (!$this->otpService->canResend($email)) {
            return [
                'success' => false,
                'message' => 'Vui lòng đợi 1 phút trước khi gửi lại OTP',
                'code' => 'RATE_LIMIT'
            ];
        }

        $otp = $this->otpService->generate($email);
        $this->otpService->setResendLimit($email);

        $user->notify(new VerifyEmailOtpNotification($otp));

        return [
            'success' => true,
            'message' => 'OTP xác minh email đã được gửi',
            'data' => [
                'expires_in_minutes' => config('otp.expiry_minutes'),
                'max_attempts' => config('otp.max_attempts')
            ]
        ];
    }

    public function verifyEmailOtp(string $email, string $otp): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email không tồn tại trong hệ thống',
            ];
        }

        if (!$this->otpService->exists($email)) {
            return [
                'success' => false,
                'message' => 'OTP không tồn tại hoặc đã hết hạn'
            ];
        }

        $isValid = $this->otpService->verify($email, $otp);

        if (!$isValid) {
            $remaining = $this->otpService->getRemainingAttempts($email);

            return [
                'success' => false,
                'message' => $remaining > 0 
                    ? "OTP không hợp lệ. Còn {$remaining} lần thử."
                    : "OTP không hợp lệ. Vui lòng yêu cầu mã mới.",
                'data' => [
                    'remaining_attempts' => $remaining
                ]
            ];
        }

        $this->userRepository->update($user->id, [
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $this->otpService->delete($email);

        return [
            'success' => true,
            'message' => 'Email đã được xác minh thành công!',
        ];
    }

    public function verifyResetPasswordOtp(string $email, string $otp): array
    {
        if(!$this->userRepository->emailExists($email)) {
            return [
                'success' => false,
                'message' => 'Email không tồn tại trong hệ thống',
            ];
        }

        if (!$this->otpService->exists($email)) {
            return [
                'success' => false,
                'message' => 'OTP không tồn tại hoặc đã hết hạn'
            ];
        }

        $remainingAttempts = $this->otpService->getRemainingAttempts($email);

        // Xác thực OTP
        $isValid = $this->otpService->verify($email, $otp);

        if (!$isValid) {
            $newRemaining = $this->otpService->getRemainingAttempts($email);

            return [
                'success' => false,
                'message' => $newRemaining > 0 
                    ? "OTP không hợp lệ. Còn {$newRemaining} lần thử."
                    : 'OTP không hợp lệ. Vui lòng yêu cầu OTP mới.',
                'data' => [
                    'remaining_attempts' => $newRemaining
                ]
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP hợp lệ. Vui lòng nhập mật khẩu mới.',
            'data' => [
                'email' => $email
            ]
        ];
    }

    public function resetPassword(string $email, string $newPassword): array
    {
        // Kiểm tra email
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email không tồn tại'
            ];
        }


        // Kiểm tra OTP đã được verified chưa
        if (!$this->otpService->isVerified($email)) {
            return [
                'success' => false,
                'message' => 'Vui lòng verify OTP trước khi đặt lại mật khẩu'
            ];
        }
        // Cập nhật mật khẩu
        $updated = $this->userRepository->updatePassword($user->id, $newPassword);

        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Không thể cập nhật mật khẩu. Vui lòng thử lại.'
            ];
        }

        // Xóa OTP sau khi reset thành công
        $this->otpService->delete($email);

        return [
            'success' => true,
            'message' => 'Mật khẩu đã được đặt lại thành công'
        ];
    }

    public function resendOtp(string $email): array
    {
        // Kiểm tra email
        if (!$this->userRepository->emailExists($email)) {
            return [
                'success' => false,
                'message' => 'Email không tồn tại'
            ];
        }

        // Kiểm tra rate limit
        if (!$this->otpService->canResend($email)) {
            return [
                'success' => false,
                'message' => 'Vui lòng đợi 1 phút trước khi gửi lại OTP',
                'code' => 'RATE_LIMIT'
            ];
        }

        $user = $this->userRepository->findByEmail($email);

        // Xóa OTP cũ và tạo mới
        $this->otpService->delete($email);
        $otp = $this->otpService->generate($email);

        // Set rate limit
        $this->otpService->setResendLimit($email);

        // Gửi OTP
        $user->notify(new SendOtpNotification($otp));

        return [
            'success' => true,
            'message' => 'Mã OTP mới đã được gửi'
        ];
    }

    public function createRandomPassword(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=';
        $charactersLength = strlen($characters);
        $randomPassword = '';
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomPassword;
    }

    public function changePassword(string $email, string $currentPassword, string $newPassword): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email không tồn tại trong hệ thống',
            ];
        }

        if (!Hash::check($currentPassword, $user->password)) {
            return [
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng',
            ];
        }

        $this->userRepository->updatePassword($user->id, $newPassword);

        return [
            'success' => true,
            'message' => 'Đổi mật khẩu thành công',
        ];
    }
    public function refreshToken (string $token): array
    {
        try {
            $newToken = JWTAuth::refresh($token);

            return [
                'success' => true,
                'message' => 'Token refreshed successfully',
                'token' => $newToken,
            ];
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return [
                'success' => false,
                'message' => 'Invalid token',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Could not refresh token: ' . $e->getMessage(),
            ];
        }
    }
    public function handleSocialLogin(string $provider, object $socialUser): array
    {
        try {
            //Tìm user theo provider và provider_id
            $user = $this->userRepository->findBySocialProvider($provider, $socialUser->id);
            
            if (!$user) {
                $user = $this->userRepository->findByEmail($socialUser->email);
                
                if ($user) {
                    // Nếu email đã tồn tại, liên kết với social provider
                    $this->userRepository->update($user->id, [
                        'provider' => $provider,
                        'provider_id' => $socialUser->id,
                        'avatar' => $socialUser->avatar ?? $user->avatar,
                    ]);
                    
                    $user->refresh();
                    
                } else {
                    $user = $this->userRepository->create([
                        'full_name' => $socialUser->name,
                        'email' => $socialUser->email,
                        'avatar' => $socialUser->avatar,
                        'provider' => $provider,
                        'provider_id' => $socialUser->id,
                        'is_verified' => true,
                        'email_verified_at' => now(),
                        'role' => UserRole::USER,
                        'status' => UserStatus::ACTIVE,
                        'password' => null,
                    ]);
                }
            } else {
                $this->userRepository->update($user->id, [
                    'avatar' => $socialUser->avatar ?? $user->avatar,
                    'full_name' => $socialUser->name ?? $user->full_name,
                ]);
                
                $user->refresh();
            }
            
            if ($user->status === UserStatus::INACTIVE) {
                return [
                    'success' => false,
                    'message' => 'Tài khoản của bạn đã bị vô hiệu hóa',
                ];
            }
            
            $token = JWTAuth::fromUser($user);
            
            return [
                'success' => true,
                'message' => 'Đăng nhập thành công',
                'user' => new UserResource($user),
                'token' => $token,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ];
        }
    }
}
