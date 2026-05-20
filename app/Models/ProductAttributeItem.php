<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property string $id
 * @property string $product_attribute_id
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $describe
 * @property-read \App\Models\ProductAttribute|null $productAttribute
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeItem whereProductAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeItem whereValue($value)
 * @mixin \Eloquent
 */
class ProductAttributeItem extends Model
{
    use HasUuids;

    protected $table = 'product_attribute_items';
    protected $guarded = [];

    public function productAttribute(): HasOne
    {
        return $this->hasOne(ProductAttribute::class, 'id', 'product_attribute_id');
    }

    public function describe(): string
    {
        return "[{$this->productAttribute->name} : {$this->value}]";
    }

    public function attributeName(): string
    {
        return $this->productAttribute->name;
    }

    public function describeOnlyValue(): string
    {
        return $this->value;
    }

    public function getDescribeAttribute(): string
    {
        return $this->describe();
    }
}
