<?php

namespace App\Models;

use App\Enums\PriceListType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $name
 * @property string $contact_group_id
 * @property PriceListType $type
 * @property float $value
 * @property string $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ContactGroup|null $contactGrpup
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList query()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList whereContactGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList whereValue($value)
 * @property-read \App\Models\ContactGroup|null $contactGroup
 * @mixin \Eloquent
 */
class PriceList extends Model
{
    use HasUuids;

    protected $table = 'price_lists';
    protected $guarded = [];

    protected $casts = [
        'type' => PriceListType::class,
    ];

    public static function calculate(PriceList $priceList, float $value): float
    {
        return $priceList->type->isPercentageBased()
            ? $value - ($value * $priceList->value / 100)
            : $value - $priceList->value;
    }

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function contactGroup(): HasOne
    {
        return $this->hasOne(ContactGroup::class, 'id', 'contact_group_id');
    }
}
