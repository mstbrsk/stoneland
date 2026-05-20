<?php

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use \Livewire\WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public bool $showDetailModal = false;

    public string $activeDetail = '';

    public string $relationId = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
        }

        if ($property === 'showDetailModal') {

        }
    }

    public function showDetail(\App\Models\CrmLead $crmLead)
    {
        $this->relationId = $crmLead->id;
        $this->activeDetail = $crmLead->notes;
        $this->showDetailModal = true;

        $this->dispatch('set-relation-id', $this->relationId);
    }

    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filtreler silindi.');
    }

    public function delete(\App\Models\CrmLead $crmLead): void
    {
        $crmLead->delete();

        $this->success("Crm kaydı silindi");
    }

    public function headers(): array
    {
        return [
            ['key' => 'proposal_no', 'label' => 'Teklif No', 'class' => 'w-64'],
            ['key' => 'contact_name', 'label' => 'Potansiyel Müşteri', 'class' => 'w-64'],
            ['key' => 'contacted_person', 'label' => 'Görüşülen Kişi', 'class' => 'w-64'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
        ];
    }

    public function leads(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\CrmLead::query()
            ->whereNotNull('proposal_no')
            ->when($this->search, fn(Builder $q) => $q->where('contact_name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function with(): array
    {
        return [
            'leads' => $this->leads(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Fırsatlar" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"/>
            <x-button label="Yeni" link="/crm/leads/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table link="/crm/leads/{id}/edit" with-pagination :headers="$headers" :rows="$leads" :sort-by="$sortBy">

            @php
                /** @var \App\Models\CrmLead $crmLead */
            @endphp
            @scope('actions', $lead)

            <x-button icon="o-trash" wire:click="delete('{{ $lead['id'] }}')" wire:confirm="Emin misiniz?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            @endscope


            @scope('cell_contact_name', $crmLead)
            @if($crmLead->hasSentProposal())
                @php
                    $proposals = $crmLead->getAllProposals();

                @endphp


                @if($proposals->isNotEmpty())
                    @foreach($proposals as $proposal)
                    <x-button
                        @click="window.location='/proposals/{{ $proposal->id }}/edit'"
                        label="Teklifi görüntüle"
                        class="btn-success w-24"
                    />

                        <x-table :headers="[
        ['key' => 'proposal_no', 'label' => 'Adı'],
        ['key' => 'quantity', 'label' => 'Adet'],
    ]" :rows="$proposals"     @click="window.location='/proposals/{{ $proposal->id }}/edit'"/>


                    @endforeach
                @else
                    <span class="badge badge-success">Teklif Gönderildi</span>
                @endif
            @endif
            @endscope

        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Search..." .../>
        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('set-relation-id', (event) => {
            messageHistoryModal.showModal();
        });
    });
</script>
