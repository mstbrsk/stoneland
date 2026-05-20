<?php

namespace App\Models;

use App\Enums\Proposal\ProposalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property string $id
 * @property string|null $relation_id
 * @property string|null $proposal_no
 * @property string|null $contact_name
 * @property string $contacted_person
 * @property string $notes
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property \Illuminate\Support\Carbon|null $contacted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Contact|null $contact
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $updatedBy
 *  * @property-read \App\Models\Proposal|null $proposal
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereContactedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereContactedPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereProposalNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereRelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class CrmLead extends Model
{
    use HasUuids;

    protected $table = 'crm_leads';
    protected $guarded = [];

    protected $casts = [
        'contacted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function info(bool $withDate = true): string
    {
        $txt = "{$this->contact_name} - {$this->contacted_person}";

        $withDate && $txt .= "({$this->contacted_at->format('d-m-Y H:i')}";

        return $txt;
    }

    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class, 'id', 'contact_id');
    }

    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }


    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'crm_lead_id','id');

    }


    public function hasSentProposal()
    {
        return $this->proposals()->where('status', ProposalStatus::DRAFT)->exists();
    }

  public function getAllProposals()
    {
        return $this->proposals()
            ->whereIn('status', [ProposalStatus::DRAFT, ProposalStatus::APPROVED, ProposalStatus::REJECTED, ProposalStatus::CONVERTED_TO_SALE])
            ->latest('updated_at')
            ->get();
    }






}
