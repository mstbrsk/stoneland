<?php

namespace App\Models;

use App\Enums\StockProcessType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property string $id
 * @property string $name
 * @property string $stock_code
 * @property float|null $sales_price
 * @property float|null $cost
 * @property int|null $unit_id
 * @property string|null $photo
 * @property string|null $product_attributes
 * @property int|null $can_purchase
 * @property int|null $can_sale
 * @property int|null $allow_negative_stock
 * @property int $warehouse_id
 * @property string $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $updatedBy
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereAllowNegativeStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCanPurchase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCanSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereProductAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSalesPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStockCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereWarehouseId($value)
 * @property-read \App\Models\Warehouse|null $warehouse
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $variants
 * @property-read int|null $variants_count
 * @property float|null $tax_rate
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereTaxRate($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasUuids;

    protected $table = 'products';
    protected $guarded = [];

    protected $casts = [
        'product_attributes' => 'array',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function warehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    public function hasVariant(): bool
    {
        return $this->variants()->count() > 1;
    }

    public function stockCount(): int
    {
        return ProductVariant::where('product_id', $this->id)->sum('stock');
    }

    public function unit(): HasOne
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }

    public function fullName(): string
    {
        return "{$this->name} ({$this->stock_code})";
    }
}


