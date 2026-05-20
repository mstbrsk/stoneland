<?php


namespace App\Models;

use App\Enums\Proposal\CargoType;
use App\Enums\Sale\SaleStatus;
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
 * @property string $sales_no
 * @property string $contact_id
 * @property array|null $selected_variants
 * @property string $currency_id
 * @property string|null $delivery_address_id
 * @property string|null $invoice_address_id
 * @property \Illuminate\Support\Carbon|null $deadline_at
 * @property string|null $price_list_id
 * @property bool|null $is_renewable
 * @property bool|null $has_receipt
 * @property string|null $payment_condition_id
 * @property int|null $quantity
 * @property float|null $sub_total
 * @property float|null $total
 * @property string|null $notes
 * @property \Illuminate\Support\Collection|null $library
 * @property bool $was_proposal
 * @property string|null $proposal_id
 * @property SaleStatus $status
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $shipment_id
 * @property string|null $sale_return_id
 * @property string|null $return_invoice_no
 * @property CargoType|null $cargo_type App\Enums\Proposal\CargoType
 * @property string|null $cargo_provider
 * @property-read \App\Models\Contact|null $contact
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Address|null $deliveryAddress
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SaleItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\PaymentCondition|null $paymentCondition
 * @property-read \App\Models\SaleReturn|null $saleReturn
 * @property-read \App\Models\User|null $updatedBy
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SaleVariant> $variants
 * @property-read int|null $variants_count
 * @method static \Illuminate\Database\Eloquent\Builder|Sale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sale query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereCargoProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereCargoType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereDeadlineAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereDeliveryAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereHasReceipt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereInvoiceAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereIsRenewable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereLibrary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale wherePaymentConditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale wherePriceListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereProposalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereReturnInvoiceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereSaleReturnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereSalesNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereSelectedVariants($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereShipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereWasProposal($value)
 * @mixin \Eloquent
 */
class Sale extends Model
{
    use HasUuids;

    protected $table = 'sales';
    protected $guarded = [];

    protected $casts = [
        'status' => SaleStatus::class,
        'deadline_at' => 'datetime',
        'library' => AsCollection::class,
        'selected_variants' => 'json',
        'cargo_type' => CargoType::class,
    ];

    protected static function booted()
    {
        static::deleting(function (self $sale) {
            $sale->items()->each(fn(SaleItem $item) => $item->delete());
        });
    }

    public function paymentCondition(): HasOne
    {
        return $this->hasOne(PaymentCondition::class,'id','payment_condition_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class, 'id', 'contact_id');
    }

    public function saleReturn(): HasOne
    {
        return $this->hasOne(SaleReturn::class, 'sale_id', 'id');
    }

    public function hasReturn(): bool
    {
        return !is_null($this->sale_return_id);
    }

    public function getSaleReturnVariantList(): array
    {
        return $this->saleReturn->returns;
    }

    public function totalPrice(): ?float
    {
        return $this->total;
    }

    public function subTotalPrice(): float
    {
        return $this->sub_total;
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
        return $this->hasMany(SaleItem::class, 'sale_id', 'id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(SaleVariant::class, 'sale_id', 'id');

    }

    public function getVariantList(string $productId): Collection
    {
        return $this->variants()->where('product_id', $productId)->get();
    }


    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function deliveryAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'delivery_address_id');
    }

    public function isProposal(): bool
    {
        return $this->status === SaleStatus::DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === SaleStatus::PENDING;
    }

    public function isCancelled(): bool
    {
        return $this->status === SaleStatus::CANCELLED;
    }

    public function isInStock(): bool
    {
        return $this->status === SaleStatus::SOLD;
    }
}
