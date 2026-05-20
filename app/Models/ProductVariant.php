<?php

namespace App\Models;

use App\Enums\StockProcessType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 *
 *
 * @property string $id
 * @property string|null $stock_code
 * @property string|null $product_name
 * @property string $product_id
 * @property string|null $attribute_items
 * @property int|null $stock
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $variant
 * @property-read string $variant_with_product_name
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereAttributeItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereStockCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereUpdatedAt($value)
 * @property string $created_by
 * @property string|null $updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereUpdatedBy($value)
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $updatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory> $warehouseProductStocks
 * @property-read int|null $warehouse_product_stocks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory> $warehouseVariantStocks
 * @property-read int|null $warehouse_variant_stocks_count
 * @mixin \Eloquent
 */
class ProductVariant extends Model
{
    use HasUuids;

    protected $table = 'product_variants';
    protected $guarded = [];

    protected $casts = [
        'attribute_items' => 'json'
    ];

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function getVariantName(string $glue = ' ', bool $withProductName = false, bool $showProductName = false): string
    {
        $itemList = $this->attribute_items;

        if (is_string($itemList)) {
            $itemList = json_decode($itemList);
        }

        $productName = $this->product->name;

        if ($withProductName && $this->product->hasVariant()) {
            $productName .= ' : ';
        }


        $result = ProductAttributeItem::whereIn('id', $itemList ?? [])
            ->get()
           ->map(fn(ProductAttributeItem $attributeItem) => $attributeItem->describeOnlyValue());




        if (!empty($glue)) {
            $result = $result->implode($glue);
        }

        return ($showProductName ? "{$productName} :" : '') . $result;
    }

    public function getAttributeName(): string
    {
        $itemList = $this->attribute_items;

        if (is_string($itemList)) {
            $itemList = json_decode($itemList);
        }

        return ProductAttributeItem::whereIn('id', $itemList ?? [])
            ->get()
            ->map(fn(ProductAttributeItem $attributeItem) => $attributeItem->attributeName())
            ->implode(' ');
    }

    public function getVariantAttribute(): string
    {
        return $this->getVariantName();
    }

    public function getVariantWithProductNameAttribute(string $glue = ' '): string
    {
        return $this->getVariantName(glue: $glue, withProductName: true);
    }

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function warehouseVariantStocks(): HasMany
    {
        return $this->hasMany(Inventory::class, 'variant_id', 'id');
    }

    public function warehouseProductStocks(): HasMany
    {
        return $this->hasMany(Inventory::class, 'product_id', 'product_id');
    }

    public function getWarehouseStocks(): array
    {
        $productInWarehouses = $this->warehouseVariantStocks
            ->groupBy('warehouse_id')
            ->map(function (Collection $list) {
                /** @var Inventory $productInWarehouse */
                $productInWarehouse = $list->get(0);

                return [
                    'name' => $productInWarehouse->warehouse->name,
                    'color' => $productInWarehouse->warehouse->color,
                    'quantity' => $list->sum('quantity'),
                ];
            })
            ->values()
            ->toArray();

        return array_map(
            function ($array, $k) {
                return sprintf('<span style="background-color:%s;">%s  %s</span>', $array['color'], $array['name'], $array['quantity']);
            },
            $productInWarehouses,
            array_keys($productInWarehouses)
        );
    }

    public function stockCount(): int
    {
        $in = $this->hasMany(ProductTransaction::class, 'product_variant_id', 'id')
            ->where('type', StockProcessType::IN->value)
            //->orWhere('type', StockProcessType::CANCEL->value)
            ->sum('quantity');

        $out = $this->hasMany(ProductTransaction::class, 'product_variant_id', 'id')
            ->where('type', StockProcessType::OUT->value)
            ->sum('quantity');

        return $in - $out;
    }
}
