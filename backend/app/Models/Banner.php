<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'title',
        'image_url',
        'link_to',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
