<?php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use BackedEnum;

class EnumCast implements CastsAttributes
{
    protected string $enum;

    public function __construct(string $enum)
    {
        $this->enum = $enum;
    }

    public function get($model, $key, $value, $attributes)
    {
        return $value === null ? null : ($this->enum)::from($value);
    }

    public function set($model, $key, $value, $attributes)
    {
        if ($value === null) return [$key => null];
        if ($value instanceof BackedEnum) $value = $value->value;
        return [$key => $value];
    }
}