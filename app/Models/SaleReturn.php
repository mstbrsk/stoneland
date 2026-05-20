<?php

namespace App\Models;

use App\Enums\Sale\SaleReturnStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $sale_id
 * @property array|null $returns json -> item_id , variant_id , qty
 * @property SaleReturnStatus $status
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn query()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn whereReturns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn whereUpdatedBy($value)
 * @property-read \App\Models\Sale|null $sale
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $updatedBy
 * @property string|null $return_invoice_no
 * @method static \Illuminate\Database\Eloquent\Builder|SaleReturn whereReturnInvoiceNo($value)
 * @mixin \Eloquent
 */
class SaleReturn extends Model
{
    use HasUuids;

    protected $table = 'sale_returns';
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => SaleReturnStatus::class,
        'returns' => 'json',
    ];

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class, 'id', 'sale_id');
    }

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function returnQty(): ?array
    {
        return $this->returns;
    }
}
