<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendMailOrderNotification extends Notification
{
    use Queueable;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // 🔥 Lấy tên khách hàng từ column full_name (MySQL)
        $customerName = $this->order->full_name ?? 'Khách hàng';
        
        // 🔥 Đảm bảo load relationship items nếu chưa có
        if (!$this->order->relationLoaded('items')) {
            $this->order->load('items');
        }
        
        // 🔥 Lấy items từ relationship
        $orderItems = $this->order->items;
        
        return (new MailMessage)
            ->subject('✓ Xác nhận đơn hàng #' . $this->order->order_code . ' - SHOP.CO')
            ->view('emails.order-confirmation', [
                'order' => $this->order,
                'customerName' => $customerName,
                'orderItems' => $orderItems
            ]);
    }
}