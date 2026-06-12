<?php

namespace App\Support;

class Money
{
    public static function format(float|string $amount, ?string $currency = null): string
    {
        $symbol = $currency ?? auth()->user()?->shopSetting?->currency ?? '৳';

        return $symbol.number_format((float) $amount, 2);
    }
}
