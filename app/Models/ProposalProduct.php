<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property string $id
 * @property string $proposal_id
 * @property string $product_id
 * @property string|null $notes
 * @property int $qty
 * @property float $unit_price
 * @property float $vat_rate
 * @property float $vat_line_total
 * @property float $line_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Proposal> $proposal
 * @property-read int|null $proposal_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereProposalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereVatLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereVatRate($value)
 * @property float|null $discount_rate
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalProduct whereDiscountRate($value)
 * @mixin \Eloquent
 */
class ProposalProduct extends Model
{
    use HasUuids;

    protected $table = 'proposal_products';
    protected $guarded = [];

    public function proposal(): HasMany
    {
        return $this->hasMany(Proposal::class, 'id', 'proposal_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
