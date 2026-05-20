<?php

namespace App\Enums\Sample;

use App\Concerns\EnumToArray;

enum SampleStatus: int
{
    use EnumToArray;

    case PENDING = 1;
    case APPROVED = 2;
    case SHIPPED = 3;
    case SOME_OF_RETURNED = 4;
    case ALL_RETURNED = 5;
    case REJECTED = 6;

    public function text(): string
    {
        return match ($this) {
            self::PENDING => 'Bekliyor',
            self::SHIPPED => 'Gönderildi',
            self::REJECTED => 'Reddedildi',
            self::SOME_OF_RETURNED => 'Eksik Olarak Geri Geldi',
            self::ALL_RETURNED => 'Hepsi Geri Geldi',
            self::APPROVED => 'Onaylandı',
        };
    }

    public function textWithBadge(): string
    {
        $text = $this->text();
        $baseClasses = 'px-3 py-1 text-sm font-semibold rounded-full';

        $styles = match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::APPROVED => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
            self::SHIPPED => 'bg-blue-100 text-blue-800',
            self::SOME_OF_RETURNED => 'bg-purple-100 text-purple-800',
            self::ALL_RETURNED => 'bg-green-100 text-gray-800',
        };

        return sprintf('<span class="%s %s">%s</span>', $baseClasses, $styles, htmlspecialchars($text));
    }

    public function shipped(): bool
    {
        return $this === self::SHIPPED;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function pending(): bool
    {
        return $this === self::PENDING;
    }

    public function someOfReturned(): bool
    {
        return $this === self::SOME_OF_RETURNED;
    }

    public function allReturned(): bool
    {
        return $this === self::ALL_RETURNED;
    }
}
