<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $sale_id
 * @property string $product_id
 * @property string $variant_id
 * @property int $qty
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Sale|null $sale
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant whereVariantId($value)
 * @property-read \App\Models\ProductVariant|null $variant
 * @property string|null $sale_item_id
 * @method static \Illuminate\Database\Eloquent\Builder|SaleVariant whereSaleItemId($value)
 * @mixin \Eloquent
 */
class SaleVariant extends Model
{
    use HasUuids;

    protected $table = 'sale_variants';
    protected $guarded = [];

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class, 'id', 'sale_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function variant(): HasOne
    {
        return $this->hasOne(ProductVariant::class, 'id', 'variant_id');
    }
}
