<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        // Helper lấy giá trị an toàn từ array hoặc object
        $get = function($key, $default = null) {
            if (is_array($this) && array_key_exists($key, $this)) {
                return $this[$key];
            }
            if (isset($this->$key)) {
                return $this->$key;
            }
            return $default;
        };

        // Lấy created_at an toàn
        $createdAt = $get('created_at');
        if ($createdAt) {
            try {
                $createdAt = is_string($createdAt)
                    ? date('d-m-Y H:i', strtotime($createdAt))
                    : $createdAt->format('d-m-Y H:i');
            } catch (\Throwable $e) {
                $createdAt = null;
            }
        }

        return [
            'id'         => (string) ($get('_id') ?? $get('id')),
            'product_id' => (string) $get('product_id'),
            'user' => [
                'id'   => (string) $get('user_id'),
                'name' => $get('user')['full_name']
                            ?? ($this->user->full_name ?? ''),
                'avatar' => $get('user')['avatar'] ?? ($this->user->avatar)            
            ],
            'rating'     => (int) $get('rating', 0),
            'content'    => $get('content', ''),

            // Always return an array for images
            'images'     => array_values((array) ($get('images') ?? [])),

            'created_at' => $createdAt,
        ];
    }
}
