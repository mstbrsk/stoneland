<?php

namespace App\Models;

use App\Enums\Purchase\PurchaseStatus;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $supplier_id
 * @property array|null $selected_items
 * @property array|null $selected_variants
 * @property string $currency_id
 * @property \Illuminate\Support\Carbon $purchased_at
 * @property \Illuminate\Support\Carbon $deadline_at
 * @property string $purchase_no
 * @property string|null $source_doc
 * @property int $warehouse_id
 * @property string|null $invoice_no
 * @property float|null $sub_total
 * @property float|null $total
 * @property string|null $notes
 * @property \Illuminate\Support\Collection|null $library
 * @property PurchaseStatus $status
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Currency|null $currency
 * @property-read Collection<int, \App\Models\PurchaseItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Contact|null $supplier
 * @property-read \App\Models\User|null $updatedBy
 * @property-read Collection<int, \App\Models\PurchaseVariant> $variants
 * @property-read int|null $variants_count
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase query()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereDeadlineAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereInvoiceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereLibrary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase wherePurchaseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase wherePurchasedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereSelectedItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereSelectedVariants($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereSourceDoc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereWarehouseId($value)
 * @property int|null $quantity
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereQuantity($value)
 * @property string|null $dispatch_no
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereDispatchNo($value)
 * @property string|null $purchase_return_id
 * @property string|null $sale_invoice_no
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase wherePurchaseReturnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase whereSaleInvoiceNo($value)
 * @property-read \App\Models\PurchaseReturn|null $purchaseReturn
 * @mixin \Eloquent
 */
class Purchase extends Model
{
    use HasUuids;

    protected $table = 'purchases';
    protected $guarded = [];

    protected $casts = [
        'status' => PurchaseStatus::class,
        'purchased_at' => 'datetime',
        'deadline_at' => 'datetime',
        'library' => AsCollection::class,
        'selected_variants' => 'json',
        'selected_items' => 'json',
    ];

    protected static function booted()
    {
        static::deleting(function (self $purchase) {
            $purchase->items()->each(fn(PurchaseItem $item) => $item->delete());
        });
    }

    public function getVariantList(string $productId): Collection
    {
        return $this->variants()->where('product_id', $productId)->get();
    }

    public function totalPrice(): ?float
    {
        return $this->total;
    }

    public function subTotalPrice(): float
    {
        return $this->totalPrice() / $this->taxRate();
    }

    public function taxRate(): float
    {
        return 1.10;
    }

    public function taxPrice(): ?float
    {
        return $this->totalPrice() - $this->subTotalPrice();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id', 'id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(PurchaseVariant::class, 'purchase_id', 'id');
    }

    public function supplier(): HasOne
    {
        return $this->hasOne(Contact::class, 'id', 'supplier_id');
    }

    public function warehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function purchaseReturn(): HasOne
    {
        return $this->hasOne(PurchaseReturn::class, 'purchase_id', 'id');
    }

    public function hasReturn(): bool
    {
        return !is_null($this->purchase_return_id);
    }

    public function getSaleReturnVariantList(): array
    {
        return $this->purchaseReturn->returns;
    }

    public function isWaitingForApproval(): bool
    {
        return $this->status === PurchaseStatus::WAITING_FOR_APPROVAL;
    }

    public function isPending(): bool
    {
        return $this->status === PurchaseStatus::PENDING;
    }

    public function isCancelled(): bool
    {
        return $this->status === PurchaseStatus::CANCELLED;
    }

    public function isInStock(): bool
    {
        return $this->status === PurchaseStatus::IN_STOCK;
    }
}
