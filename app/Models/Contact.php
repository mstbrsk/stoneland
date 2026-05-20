<?php

namespace App\Models;

use App\Enums\Address\AddressType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string|null $user_id
 * @property int $company_type
 * @property string|null $address
 * @property string|null $second_address
 * @property string|null $district
 * @property int|null $city_id
 * @property int|null $country
 * @property string|null $tax_administration
 * @property string|null $tax_number
 * @property string|null $phone
 * @property string|null $mobile
 * @property string|null $email
 * @property string|null $website
 * @property string|null $language
 * @property array|null $tickets
 * @property int $is_supplier
 * @property string|null $payment_condition_id
 * @property string|null $exchange_id
 * @property string|null $price_list_id
 * @property string|null $shipping_type_id
 * @property string|null $pos_campaign_id
 * @property string|null $financial_condition_id
 * @property string|null $currency_id
 * @property string $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Contact newModelQuery()
 * @method static Builder|Contact newQuery()
 * @method static Builder|Contact query()
 * @method static Builder|Contact suppliers()
 * @method static Builder|Contact whereAddress($value)
 * @method static Builder|Contact whereCityId($value)
 * @method static Builder|Contact whereCode($value)
 * @method static Builder|Contact whereCompanyType($value)
 * @method static Builder|Contact whereCountry($value)
 * @method static Builder|Contact whereCreatedAt($value)
 * @method static Builder|Contact whereCreatedBy($value)
 * @method static Builder|Contact whereCurrencyId($value)
 * @method static Builder|Contact whereDistrict($value)
 * @method static Builder|Contact whereEmail($value)
 * @method static Builder|Contact whereExchangeId($value)
 * @method static Builder|Contact whereFinancialConditionId($value)
 * @method static Builder|Contact whereId($value)
 * @method static Builder|Contact whereIsSupplier($value)
 * @method static Builder|Contact whereLanguage($value)
 * @method static Builder|Contact whereMobile($value)
 * @method static Builder|Contact whereName($value)
 * @method static Builder|Contact wherePaymentConditionId($value)
 * @method static Builder|Contact wherePhone($value)
 * @method static Builder|Contact wherePosCampaignId($value)
 * @method static Builder|Contact wherePriceListId($value)
 * @method static Builder|Contact whereSecondAddress($value)
 * @method static Builder|Contact whereShippingTypeId($value)
 * @method static Builder|Contact whereTaxAdministration($value)
 * @method static Builder|Contact whereTaxNumber($value)
 * @method static Builder|Contact whereTickets($value)
 * @method static Builder|Contact whereUpdatedAt($value)
 * @method static Builder|Contact whereUpdatedBy($value)
 * @method static Builder|Contact whereUserId($value)
 * @method static Builder|Contact whereWebsite($value)
 * @property string|null $photo
 * @method static Builder|Contact wherePhoto($value)
 * @property-read \App\Models\City|null $city
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $updatedBy
 * @property string|null $accounting_phone
 * @method static Builder|Contact whereAccountingPhone($value)
 * @property string|null $group_id
 * @method static Builder|Contact whereGroupId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Address> $addresses
 * @property-read int|null $addresses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Address> $deliveryAddresses
 * @property-read int|null $delivery_addresses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Address> $invoiceAddresses
 * @property-read int|null $invoice_addresses_count
 * @property bool $is_same
 * @property string|null $notes
 * @property-read \App\Models\TaxOffice|null $taxAdministration
 * @method static Builder|Contact whereIsSame($value)
 * @method static Builder|Contact whereNotes($value)
 * @property string|null $post_code
 * @method static Builder|Contact wherePostCode($value)
 * @mixin \Eloquent
 */
class Contact extends Model
{
    use HasUuids;

    protected $table = 'contacts';
    protected $guarded = [];

    protected $casts = [
        'tickets' => 'json',
    ];

    protected static function booted()
    {
        static::creating(function (self $self) {
            //
        });
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'contact_id', 'id');
    }

    public function deliveryAddresses(): HasMany
    {
        return $this->addresses()->where('type', AddressType::DELIVERY);
    }

    public function invoiceAddresses(): HasMany
    {
        return $this->addresses()->where('type', AddressType::INVOICE);
    }

    public function scopeSuppliers(Builder $builder)
    {
        $builder->where('is_supplier', true);
    }

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function city(): HasOne
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }

    public function ticketList(): string
    {
        return Tag::whereIn('id', $this->tickets ?? [])->get()->map(fn(Tag $tag) => $tag->name)->implode('');
    }

    public function taxAdministration(): HasOne
    {
        return $this->hasOne(TaxOffice::class, 'id', 'tax_administration');
    }
}



