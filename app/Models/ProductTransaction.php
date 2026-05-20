<?php

namespace App\Models;

use App\Enums\ProductStock\RelationType;
use App\Enums\StockProcessType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property string $product_id
 * @property int $quantity
 * @property StockProcessType $type
 * @property RelationType $relation_type
 * @property string|null $relation_id
 * @property int $warehouse_id
 * @property string|null $contact_id
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Contact|null $contact
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Purchase|null $purchase
 * @property-read \App\Models\Sale|null $sale
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereRelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereRelationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction withoutTrashed()
 * @property string|null $variant_id
 * @property string|null $notes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductTransaction> $inProducts
 * @property-read int|null $in_products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductTransaction> $outProducts
 * @property-read int|null $out_products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductTransaction> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductTransaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTransaction whereVariantId($value)
 * @property-read \App\Models\SaleReturn|null $saleReturn
 * @property-read \App\Models\PurchaseReturn|null $purchaseReturn
 * @property-read \App\Models\Sample|null $sample
 * @mixin \Eloquent
 */
class ProductTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'product_transactions';
    protected $guarded = [];

    public const UPDATED_AT = null;

    protected $casts = [
        'type' => StockProcessType::class,
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'relation_type' => RelationType::class,
    ];

    public function getVariantListAsTable(string $productId): array
    {
        return $this->purchase->getVariantList($productId)
            ->filter(fn($variant) => $variant->qty > 0)
            ->map(fn(PurchaseVariant $purchaseVariant) => [
                'name' => $purchaseVariant->variant->getVariantName(),
                'qty' => $purchaseVariant->qty
            ])
            ->toArray();
    }


    public function getVariantSaleListAsTable(string $productId): array
    {
        return $this->sale->getVariantList($productId)
            ->map(fn(SaleVariant $saleVariant) => [
                'name' => $saleVariant->variant->getVariantName(),
                'qty' => $saleVariant->qty
            ])
            ->toArray();
    }

    public function getLineTotalQuantity()
    {
        return $this->transactions()->sum('quantity');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ProductTransaction::class, 'relation_id', 'relation_id');
    }

    public function inProducts(): HasMany
    {
        return $this->products()->whereIn('type', StockProcessType::inTypes());
    }

    public function outProducts(): HasMany
    {
        return $this->products()->whereIn('type', StockProcessType::outTypes());
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductTransaction::class, 'product_id', 'product_id');
    }

    public function textWithPurchaseNo(): string
    {
        return sprintf('%s (%s)', $this->relation_type->text(), $this->purchase->purchase_no);
    }

    public function textWithSampleNo(): string
    {
        return sprintf('%s (%s)', $this->relation_type->text(), $this->sample->sample_no);
    }

    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class, 'id', 'relation_id');
    }

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class, 'id', 'relation_id');
    }

    public function sample(): HasOne
    {
        return $this->hasOne(Sample::class, 'id', 'relation_id');
    }

    public function saleReturn(): HasOne
    {
        return $this->hasOne(SaleReturn::class, 'id', 'relation_id');
    }

    public function purchaseReturn(): HasOne
    {
        return $this->hasOne(PurchaseReturn::class, 'id', 'relation_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class, 'id', 'contact_id');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function warehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }
}
