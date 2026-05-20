<?php

namespace App\Enums\Proposal;

use App\Concerns\EnumToArray;

enum PdfPrintType: int
{
    use EnumToArray;

    case WITH_PRICE = 1;
    case WITHOUT_PRICE = 2;

    public function text(): string
    {
        return match ($this) {
            self::WITH_PRICE => 'Fiyatlı',
            self::WITHOUT_PRICE => 'Fiyatsız',
        };
    }

    public function withPrice(): bool
    {
        return $this === self::WITH_PRICE;
    }

    public function withoutPrice(): bool
    {
        return $this === self::WITHOUT_PRICE;
    }
}
