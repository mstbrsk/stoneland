<?php

namespace App\Enums\ProductStock;

use App\Concerns\EnumToArray;

enum RelationType: string
{
    use EnumToArray;

    case PURCHASE = 'Purchase';
    case SALE = 'Sale';
    case WAREHOUSE_TRANSFER = 'WarehouseTransfer';
    case SAMPLE = 'Sample';

    case SALE_RETURN = 'SaleReturn';
    case PURCHASE_RETURN = 'PurchaseReturn';

    public function isPurchase(): bool
    {
        return $this === self::PURCHASE;
    }

    public function isSale(): bool
    {
        return $this === self::SALE;
    }

    public function isSample(): bool
    {
        return $this === self::SAMPLE;
    }

    public function isSaleReturn(): bool
    {
        return $this === self::SALE_RETURN;
    }

    public function isPurchaseReturn(): bool
    {
        return $this === self::PURCHASE_RETURN;
    }

    public function isWarehouseTransfer(): bool
    {
        return $this === self::WAREHOUSE_TRANSFER;
    }

    public function text(): string
    {
        return match ($this) {
            self::PURCHASE => 'Satın Alma',
            self::SALE => 'Satış',
            self::WAREHOUSE_TRANSFER => 'Depo Transferi',
            self::SAMPLE => 'Numune',
            self::SALE_RETURN => 'Satış İade',
            self::PURCHASE_RETURN => 'Satın Alma İade',
        };
    }
}
