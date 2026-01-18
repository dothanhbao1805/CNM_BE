<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailOtpNotification extends Notification
{
    use Queueable;

    protected $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Xác minh Email đăng ký')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Dưới đây là mã OTP để xác minh email của bạn:')
            ->line('**' . $this->otp . '**')
            ->line('Mã OTP sẽ hết hạn trong ' . config('otp.expiry_minutes') . ' phút.')
            ->line('Nếu bạn không thực hiện đăng ký, hãy bỏ qua email này.');
    }
}
