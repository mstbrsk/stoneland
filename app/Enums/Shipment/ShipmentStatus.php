<?php

namespace App\Enums\Shipment;

use App\Concerns\EnumToArray;
use App\Enums\StockProcessType;

enum ShipmentStatus: int
{
    use EnumToArray;

    case PENDING = 1;
    case MISSING = 2;
    case SHIPPED = 3;

    public function text(): string
    {
        return match ($this) {
            self::PENDING => 'Bekliyor',
            self::MISSING => 'Eksik Gönderim',
            self::SHIPPED => 'Tamamlandı',
        };
    }

    public function textWithBadge(): string
    {
        $text = $this->text();
        $baseClasses = 'px-2 py-1 rounded-full text-xs font-medium inline-flex items-center';

        $styles = match ($this) {
            self::PENDING => 'background-color: #FEE2E2; color: #991B1B;',
            self::MISSING => 'background-color: #FEF3C7; color: #92400E;',
            self::SHIPPED => 'background-color: #F3F4F6; color: #065F46;',
        };

        return sprintf('<span class="%s" style="%s">%s</span>', $baseClasses, $styles, htmlspecialchars($text));
    }

    public function isShipped(): bool
    {
        return $this === self::SHIPPED;
    }
}
