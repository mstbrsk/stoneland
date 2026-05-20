<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property int $from
 * @property int $to
 * @property string $transfer_id
 * @property string $product_id
 * @property string|null $variant_id
 * @property int $qty
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Warehouse|null $fromWarehouse
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Warehouse|null $toWarehouse
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereTransferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseTransfer whereVariantId($value)
 * @property-read \App\Models\ProductVariant|null $variant
 * @mixin \Eloquent
 */
class WarehouseTransfer extends Model
{
    use HasUuids;

    protected $table = 'warehouse_transfers';
    protected $guarded = [];

    public function fromWarehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class,'id','from');
    }

    public function toWarehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class,'id','to');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class,'id','product_id');
    }

    public function variant(): HasOne
    {
        return $this->hasOne(ProductVariant::class,'id','variant_id');
    }
}
