<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $purchase_id
 * @property string $product_id
 * @property string $variant_id
 * @property int $qty
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Sale|null $purchase
 * @property-read \App\Models\ProductVariant|null $variant
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant whereVariantId($value)
 * @property string|null $purchase_item_id
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseVariant wherePurchaseItemId($value)
 * @mixin \Eloquent
 */
class PurchaseVariant extends Model
{
    use HasUuids;

    protected $table = 'purchase_variants';
    protected $guarded = [];

    public function purchase(): HasOne
    {
        return $this->hasOne(Sale::class, 'id', 'purchase_id');
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
