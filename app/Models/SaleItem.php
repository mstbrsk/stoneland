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
 * @property string $sale_id
 * @property bool $receipt
 * @property string $product_id
 * @property string|null $notes
 * @property int $qty
 * @property float $unit_price
 * @property float $vat_rate
 * @property float $vat_line_total
 * @property float $line_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Sale|null $sale
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereReceipt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereVatLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereVatRate($value)
 * @property string|null $selected_variants
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereSelectedVariants($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SaleVariant> $variants
 * @property-read int|null $variants_count
 * @property string|null $discount_rate
 * @method static \Illuminate\Database\Eloquent\Builder|SaleItem whereDiscountRate($value)
 * @mixin \Eloquent
 */
class SaleItem extends Model
{
    use HasUuids;

    protected $table = 'sale_items';
    protected $guarded = [];

    protected $casts = [
        'selected_variants' => 'json',
    ];


    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class, 'id', 'sale_id');
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
        return $this->hasMany(SaleVariant::class, 'sale_item_id', 'id');
    }




}
