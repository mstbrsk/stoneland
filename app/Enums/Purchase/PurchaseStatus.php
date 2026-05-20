<?php

namespace App\Enums\Purchase;

use App\Concerns\EnumToArray;

enum PurchaseStatus: int
{
    use EnumToArray;

    case WAITING_FOR_APPROVAL = 1;
    case PENDING = 2;
    case IN_STOCK = 3;
    case CANCELLED = 4;

    public function text(): string
    {
        return match ($this) {
            self::WAITING_FOR_APPROVAL => 'Onay Bekleniyor',
            self::PENDING => 'Bekleniyor',
            self::IN_STOCK => 'Stokta',
            self::CANCELLED => 'İptal',
        };
    }

    public function editable(): bool
    {
        return in_array($this, [
            self::WAITING_FOR_APPROVAL,
        ]);
    }

    public function notEditable(): bool
    {
        return !$this->editable();
    }

    public function isInStock(): bool
    {
        return $this === self::IN_STOCK;
    }
}
