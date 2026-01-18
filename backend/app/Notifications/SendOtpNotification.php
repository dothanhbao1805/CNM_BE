<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification
{
    use Queueable;

    public $otp;

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
            ->subject('Mã OTP Reset Password')
            ->greeting('Xin chào!')
            ->line('Bạn nhận được email này vì có yêu cầu đặt lại mật khẩu cho tài khoản của bạn.')
            ->line('Mã OTP của bạn là:')
            ->line('## **' . $this->otp . '**')
            ->line('Mã này sẽ hết hạn sau **' . config('otp.expiry_minutes') . ' phút**.')
            ->line('Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.')
            ->salutation('Trân trọng, ' . config('app.name'));
    }
}