<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $relation_type
 * @property string|null $relation_id
 * @property string $notes
 * @property string $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User|null $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|ActionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ActionLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionLog whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionLog whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionLog whereRelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActionLog whereRelationType($value)
 * @mixin \Eloquent
 */
class ActionLog extends Model
{
    use HasUuids;

    protected $table = 'action_logs';
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
