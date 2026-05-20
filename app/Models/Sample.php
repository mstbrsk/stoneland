<?php

namespace App\Models;

use App\Enums\Sample\SampleStatus;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property bool $has_contact
 * @property string|null $contact_id
 * @property int|null $warehouse_id
 * @property string|null $contact_name
 * @property string|null $invoice_no
 * @property array|null $data
 * @property array|null $return_data
 * @property \Illuminate\Support\Collection|null $library
 * @property SampleStatus $status
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $shipped_by
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Contact|null $contact
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|Sample newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sample newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sample query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereHasContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereInvoiceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereLibrary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereReturnData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereShippedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereShippedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereWarehouseId($value)
 * @property string|null $sample_no
 * @method static \Illuminate\Database\Eloquent\Builder|Sample whereSampleNo($value)
 * @mixin \Eloquent
 */
class Sample extends Model
{
    use HasUuids;

    protected $table = 'samples';
    protected $guarded = [];

    protected $casts = [
        'library' => AsCollection::class,
        'data' => 'json',
        'return_data' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'shipped_at' => 'datetime',
        'status' => SampleStatus::class,
    ];

    public function getContactName(): ?string
    {
        return $this->has_contact
            ? $this->contact?->name
            : $this->contact_name;
    }

    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class, 'id', 'contact_id');
    }

    public function warehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }
}

