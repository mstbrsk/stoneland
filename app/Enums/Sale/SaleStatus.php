<?php

namespace App\Enums\Sale;

use App\Concerns\EnumToArray;

enum SaleStatus: int
{
    use EnumToArray;

    case WAS_PROPOSAL = 1;
    case DRAFT = 2;
    case PENDING = 3;
    case SOLD = 4;
    case CANCELLED = 5;

    public function text(): string
    {
        return match ($this) {
            self::WAS_PROPOSAL => 'Tekliften Satışa',
            self::DRAFT => 'Taslak',
            self::PENDING => 'Bekleniyor',
            self::SOLD => 'Satıldı',
            self::CANCELLED => 'İptal',
        };
    }

    public static function editableStatus(): array
    {
        return [
            self::WAS_PROPOSAL,
            self::DRAFT,
        ];
    }

    public function editable(): bool
    {
        return in_array($this, [
            self::WAS_PROPOSAL,
            self::DRAFT,
        ]);
    }


    public function readonly(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::SOLD,
            self::CANCELLED,
        ]);
    }

    public function notEditable(): bool
    {
        return !$this->editable();
    }

    public function sold(): bool
    {
        return $this === self::SOLD;
    }
}
