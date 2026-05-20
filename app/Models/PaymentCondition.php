<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\PaymentCondition
 *
 * @property string $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCondition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCondition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCondition whereUpdatedAt($value)
 * @property string $created_by
 * @property string|null $updated_by
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCondition whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCondition whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class PaymentCondition extends Model
{
    use HasUuids;

    protected $table = 'payment_conditions';
    protected $guarded = [];

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

}
