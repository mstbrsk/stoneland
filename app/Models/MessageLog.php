<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property-read \App\Models\User|null $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|MessageLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageLog query()
 * @property string $id
 * @property string $relation_type
 * @property string|null $relation_id
 * @property string $message
 * @property string $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|MessageLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageLog whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageLog whereRelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageLog whereRelationType($value)
 * @mixin \Eloquent
 */
class MessageLog extends Model
{
    use HasUuids;

    protected $table = 'message_logs';
    protected $guarded = [];

    public const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
}
