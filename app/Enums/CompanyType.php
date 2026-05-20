<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum CompanyType: int
{
    use EnumToArray;

    case SOLO_TRADER = 1;
    case COMPANY = 2;

    public function text(): string
    {
        return match ($this) {
            self::SOLO_TRADER => 'Şahıs',
            self::COMPANY => 'Şirket',
        };
    }
}
