<?php

namespace App\Enums\Proposal;

use App\Concerns\EnumToArray;

enum CargoType: int
{
    use EnumToArray;

    case US = 1;
    case OTHER = 2;

    public function text(): string
    {
        return match ($this) {
            self::US => 'Tarafımıza Ait',
            self::OTHER => 'Müşteriye Ait',
        };
    }

    public function byUs(): bool
    {
        return $this === self::US;
    }

    public function byOther(): bool
    {
        return $this === self::OTHER;
    }
}
