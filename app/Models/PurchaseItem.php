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
 * @property string|null $notes
 * @property int $qty
 * @property float $unit_price
 * @property float $vat_rate
 * @property float $vat_line_total
 * @property float $line_total
 * @property array|null $selected_variants
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Purchase|null $purchase
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseVariant> $variants
 * @property-read int|null $variants_count
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereSelectedVariants($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereVatLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseItem whereVatRate($value)
 * @mixin \Eloquent
 */
class PurchaseItem extends Model
{
    use HasUuids;

    protected $table = 'purchase_items';
    protected $guarded = [];

    protected $casts = [
        'selected_variants' => 'json',
    ];

    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class, 'id', 'purchase_id');
    }

    public function totalPrice(): float|int
    {
        return $this->unit_price * $this->qty;
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(PurchaseVariant::class, 'purchase_id', 'purchase_id')
            ->where('product_id', $this->product_id)->where('purchase_item_id', $this->id);
    }




    /*public function variants(): HasMany
    {
        $selectedVariants = $this->selected_variants;
        $selectedVariantsArray = $selectedVariants;
        $uuidKeys = array_keys($selectedVariantsArray);

         return $this->hasMany(PurchaseVariant::class, 'purchase_id', 'purchase_id')
             ->whereIn('variant_id', $uuidKeys)->where('purchase_item_id', $this->id);


    }*/


}
