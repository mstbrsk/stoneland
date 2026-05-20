<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum PriceListType: int
{
    use EnumToArray;

    case PERCENTAGE_BASED = 1;
    case AMOUNT_BASED = 2;

    public function text(): string
    {
        return match ($this) {
            self::PERCENTAGE_BASED => 'Yüzde',
            self::AMOUNT_BASED => 'Değer',
        };
    }

    public function sign(): string
    {
        return match ($this) {
            self::PERCENTAGE_BASED => '%',
            self::AMOUNT_BASED => '',
        };
    }

    public function isPercentageBased(): bool
    {
        return $this === self::PERCENTAGE_BASED;
    }

    public function isAmountBased(): bool
    {
        return $this === self::AMOUNT_BASED;
    }
}
