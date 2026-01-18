<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'fullName'  => $this->full_name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'phoneNumber' => $this->phone_number,
            'avatar' => $this->avatar,
            'gender' => $this->gender, 
        ];
    }
}
