<?php

namespace App\Models;

use App\Enums\Proposal\ProposalStatus;
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
 * @property string $proposal_no
 * @property bool $has_contact
 * @property string|null $contact_id
 * @property string|null $delivery_address_id
 * @property string|null $invoice_address_id
 * @property string|null $crm_lead_id
 * @property string|null $delivery_address
 * @property string|null $invoice_address
 * @property string $currency_id
 * @property array|null $selected_items
 * @property \Illuminate\Support\Carbon|null $deadline_at
 * @property string|null $price_list_id
 * @property bool|null $is_renewable
 * @property string|null $payment_condition_id
 * @property int|null $quantity
 * @property float|null $sub_total
 * @property float|null $total
 * @property string|null $notes
 * @property \Illuminate\Support\Collection|null $library
 * @property ProposalStatus $status
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array|null $tags
 * @property string|null $cargo_provider
 * @property-read \App\Models\Contact|null $contact
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Currency|null $currency
 * @property-read \App\Models\Address|null $deliveryAddress
 * @property-read \App\Models\Address|null $invoiceAddress
 * @property-read \App\Models\CrmLead|null $lead
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProposalProduct> $products
 * @property-read int|null $products_count
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCargoProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCargoType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCrmLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereDeadlineAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereDeliveryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereDeliveryAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereHasContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereInvoiceAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereInvoiceAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereIsRenewable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereLibrary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal wherePaymentConditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal wherePriceListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereProposalNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereSelectedItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereUpdatedBy($value)
 * @property \App\Enums\Proposal\CargoType|null $cargo_type App\Enums\Proposal\ProposalCargoType
 * @mixin \Eloquent
 */
class Proposal extends Model
{
    use HasUuids;

    protected $table = 'proposals';
    protected $guarded = [];

    protected $casts = [
        'deadline_at' => 'datetime',
        'library' => AsCollection::class,
        'status' => \App\Enums\Proposal\ProposalStatus::class,
        'cargo_type' => \App\Enums\Proposal\CargoType::class,
        'tags' => 'array',
        'selected_items' => 'json',
    ];

    public function getContactName(): ?string
    {
        return $this->has_contact
            ? $this->contact?->name
            : $this->lead->info(withDate: false);
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->has_contact
            ? $this->deliveryAddress?->name
            : $this->delivery_address;
    }

    public function geInvoiceAddress(): ?string
    {
        return $this->has_contact
            ? $this->invoiceAddress->name
            : $this->invoice_address;
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProposalProduct::class, 'proposal_id', 'id');
    }

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function deliveryAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'delivery_address_id');
    }

    public function invoiceAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'invoice_address_id');
    }

    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class, 'id', 'contact_id');
    }

    public function lead(): HasOne
    {
        return $this->hasOne(CrmLead::class, 'id', 'crm_lead_id');
    }





    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function totalPrice(): ?float
    {
        return $this->total;
    }

    public function subTotalPrice(): float
    {
        return $this->sub_total;
    }

    public function taxPrice(): ?float
    {
        return $this->totalPrice() - $this->subTotalPrice();
    }

    public function makeArchive(): void
    {
        $this->update([
            'status' => ProposalStatus::ARCHIVE
        ]);
    }
}
