<?php

namespace App\Enums\Purchase;

use App\Concerns\EnumToArray;

enum PurchaseReturnStatus: int
{
    use EnumToArray;

    case PENDING = 1;
    case DONE = 2;

    public function text(): string
    {
        return match ($this) {
            self::PENDING => 'Bekliyor',
            self::DONE => 'Tamamlandı',
        };
    }

    public function textWithBadge(): string
    {
        $text = $this->text();
        $baseClasses = 'px-2 py-1 rounded-full text-xs font-medium inline-flex items-center';

        $styles = match ($this) {
            self::PENDING => 'background-color: #FEE2E2; color: #991B1B;',
            self::DONE => 'background-color: #F3F4F6; color: #065F46;',
        };

        return sprintf('<span class="%s" style="%s">%s</span>', $baseClasses, $styles, htmlspecialchars($text));
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#FEE2E2',
            self::DONE => '#F3F4F6',
        };
    }

    public function isDone(): bool
    {
        return $this === self::DONE;
    }
}
