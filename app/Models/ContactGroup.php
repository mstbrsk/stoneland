<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $name
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ContactGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactGroup whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactGroup whereUpdatedBy($value)
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $updatedBy
 * @mixin \Eloquent
 */
class ContactGroup extends Model
{
    use HasUuids;

    protected $table = 'contact_groups';
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
