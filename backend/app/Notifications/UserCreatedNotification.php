<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification
{
    use Queueable;

    public $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tài khoản của bạn đã được tạo')
            ->greeting('Xin chào ' . $notifiable->name . ',')
            ->line('Tài khoản của bạn đã được tạo thành công.')
            ->line('Dưới đây là mật khẩu đăng nhập:')
            ->line('**' . $this->password . '**')
            ->line('Bạn hãy đăng nhập và đổi mật khẩu ngay lập tức để bảo mật.')
            ->salutation('Trân trọng, đội ngũ hỗ trợ.');
    }
}
