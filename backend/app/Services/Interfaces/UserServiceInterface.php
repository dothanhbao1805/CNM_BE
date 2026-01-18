<?php
namespace App\Services\Interfaces;
use App\Models\User;
interface UserServiceInterface
{
    public function getAllUsers();
    public function getUserById(string $id);
    public function createUser(array $data);
    public function updateUser(User $user, array $data); 
    public function deleteUser(User $user); 
    public function register(array $data);
    public function login(array $credentials);
    public function sendPasswordResetOtp(string $email): array;
    public function verifyResetPasswordOtp(string $email, string $otp): array;
    public function resetPassword(string $email, string $newPassword): array;
    public function resendOtp(string $email): array;
    public function sendVerifyEmailOtp(string $email): array;
    public function verifyEmailOtp(string $email, string $otp): array;
    public function changePassword(string $email, string $currentPassword, string $newPassword): array;
    public function refreshToken (string $token): array;
    public function handleSocialLogin(string $provider, object $socialUser): array;
}