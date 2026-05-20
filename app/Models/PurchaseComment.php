<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\PurchaseComment
 *
 * @property string $id
 * @property array $content
 * @property string $user_id
 * @property string $purchase_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Administrator|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseComment whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseComment wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseComment whereUserId($value)
 * @mixin \Eloquent
 */
class PurchaseComment extends Model
{
    use HasUuids;

    protected $table = 'purchase_comments';
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'content' => 'json',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(Administrator::class, 'id', 'user_id');
    }
}
