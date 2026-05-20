<?php

namespace App\Enums\Address;

use App\Concerns\EnumToArray;

enum AddressType: int
{
    use EnumToArray;

    case DELIVERY = 1;
    case INVOICE = 2;

    public function text(): string
    {
        return match ($this) {
            self::DELIVERY => 'Sevkiyat Adresi',
            self::INVOICE => 'Fatura Adresi',
        };
    }
}
