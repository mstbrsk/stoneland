<?php

namespace App\Enums;

use App\Concerns\EnumToArray;
use function Laravel\Prompts\select;

enum StockProcessType: string
{
    use EnumToArray;

    case IN = 'in';
    case OUT = 'sale';
    case CANCEL = 'cancel';
    case TRANSFER = 'transfer';

    case PRODUCTION = 'production';
    case SAMPLE = 'sample';

    public function text(): string
    {
        return match ($this) {
            StockProcessType::IN => 'Giriş',
            StockProcessType::OUT => 'Çıkış',
            StockProcessType::CANCEL => 'İptal/Silme',
            StockProcessType::PRODUCTION => 'Üretim',
            StockProcessType::SAMPLE => 'Numune',
            StockProcessType::TRANSFER => 'Transfer',
        };
    }

    public static function inTypes(): array
    {
        return [
            self::IN,
            self::PRODUCTION,
        ];
    }

    public static function outTypes(): array
    {
        return [
            self::OUT,
        ];
    }

    public function isIn(): bool
    {
        return $this === self::IN;
    }

    public function isTransfer(): bool
    {
        return $this === self::TRANSFER;
    }


    public function isOut(): bool
    {
        return $this === self::OUT;
    }

    public function textWithBadge(): string
    {
        $text = $this::text();

        return match ($this) {
            self::IN => "<span class='text-green-500'>{$text}</span>",
            self::OUT, self::CANCEL, => "<span class='text-red-500'>{$text}</span>",
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN => 'green',
            self::OUT, self::CANCEL, => 'red',
        };
    }

    public function sign(): string
    {
        return match ($this) {
            self::IN, self::CANCEL, => '+',
            self::OUT => '-',
        };
    }
}
