<?php

namespace App\Models;

use App\Enums\Purchase\PurchaseReturnStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property string $id
 * @property string $purchase_id
 * @property array|null $returns json -> item_id , variant_id , qty
 * @property PurchaseReturnStatus $status
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Purchase|null $purchase
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereReturns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereUpdatedBy($value)
 * @property string|null $sale_invoice_no
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereSaleInvoiceNo($value)
 * @mixin \Eloquent
 */
class PurchaseReturn extends Model
{
    use HasUuids;

    protected $table = 'purchase_returns';
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => PurchaseReturnStatus::class,
        'returns' => 'json',
    ];

    public function getReturns(): array
    {
        return collect($this->returns)->map(fn(array $return) => [
            'variant_name' => ProductVariant::findOrFail($return['variant_id'])->getVariantName(),

            'return_qty' => $return['qty'],
            'stock_code' => ProductVariant::findOrFail($return['variant_id'])->stock_code,
            'product_name' => ProductVariant::findOrFail($return['variant_id'])->product_name,
            'purchased_qty' => PurchaseVariant::firstWhere('variant_id', $return['variant_id'])->value('qty'),
        ])
            ->toArray();
    }

    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class, 'id', 'purchase_id');
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
