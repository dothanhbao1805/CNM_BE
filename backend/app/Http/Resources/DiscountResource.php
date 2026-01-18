<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->_id,
            'code'            => $this->code,
            'description'     => $this->description,
            'type'            => $this->type,              // percent | fixed
            'value'           => $this->value,             // giá trị giảm
            'min_order_value' => $this->min_order_value,
            'max_discount'    => $this->max_discount,
            'usage_limit'     => $this->usage_limit,
            'used'            => $this->used,
            'start_date'      => $this->start_date,
            'end_date'        => $this->end_date,
            'is_active'       => $this->is_active,

            // ✅ Tự động trả về số lần còn có thể dùng nếu hữu ích
            'remaining_uses'  => $this->usage_limit !== null
                                    ? max(0, $this->usage_limit - ($this->used ?? 0))
                                    : null,
        ];
    }
}
