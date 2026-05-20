<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $shipment_id
 * @property string $sale_id
 * @property string $product_id
 * @property string $variant_id
 * @property bool $can_printable
 * @property int $shipped_qty
 * @property string $delivery_address_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $sale_variant_id
 * @property int|null $sale_variant_qty
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Sale|null $sale
 * @property-read \App\Models\Shipment|null $shipment
 * @property-read \App\Models\ProductVariant|null $variant
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereDeliveryAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereSaleVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereSaleVariantQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereShipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereShippedQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentItem whereCanPrintable($value)
 * @mixin \Eloquent
 */
class ShipmentItem extends Model
{
    use HasUuids;

    protected $table = 'shipment_items';
    protected $guarded = [];
    protected $casts = [
        'can_printable' => 'boolean',
    ];
    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class, 'id', 'shipment_id');
    }

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

    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'delivery_address_id');
    }
}
