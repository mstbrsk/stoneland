<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum StockType: string
{
    use EnumToArray;

    case SEMI_PRODUCT = 'YM'; // S kodu olabilir
    case PRODUCT = 'BRK'; // BRK kodu
    case DIRECT_BUY = 'S'; // Bu da S kodu olabilir daha sonra stoğa alıp BRK yapabilir

    public function text(): string
    {
        return match ($this) {
            self::SEMI_PRODUCT => 'Yarı Mamül',
            self::PRODUCT => 'Mamül',
            self::DIRECT_BUY => 'Direk Alış',
        };
    }
}
