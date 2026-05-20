<?php

use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use \Livewire\WithPagination;

    public string $search = '';
    public ?array $status = [];
    public int $country_id = 0;
    public bool $include_archives = false;
    public bool $drawer = false;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public bool $showDetailModal = false;
    public bool $showUnArchiveModal = false;

    public ?\App\Models\Proposal $selectedProposal = null;

    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
        }
    }

    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filtreler temizlendi.');
    }

    public function setStatusFilter($status): void
    {
        $this->status = $status ? [$status] : null;

        $this->resetPage();
    }

    public function showDetails(\App\Models\Proposal $proposal): void
    {
        $this->selectedProposal = $proposal;

        $this->showDetailModal = true;
    }

    public function unArchiveModal(\App\Models\Proposal $proposal): void
    {
        $this->selectedProposal = $proposal;

        $this->showUnArchiveModal = true;
    }

    public function unArchive(): void
    {
        if (!$this->selectedProposal->status->isArchive()) {
            throw \Mary\Exceptions\ToastException::error('Teklif henüz arşivlenmemiş!');
        }

        $this->selectedProposal->update([
            'status' => \App\Enums\Proposal\ProposalStatus::DRAFT,
        ]);

        $this->success('Teklif arşivden çıkarıldı');

        log_action(message: 'Teklif arşivden çıkarıldı', relationType: 'Proposal', relationId: $this->selectedProposal->id);

        $this->showUnArchiveModal = false;
    }

    public function headers(): array
    {
        return [
            ['key' => 'proposal_no', 'label' => 'Teklif No'],
            ['key' => 'contact_name', 'label' => 'Müşteri', 'class' => 'w-64'],
            ['key' => 'created_at', 'label' => 'Açılış Tarihi', 'class' => 'w-64'],
            ['key' => 'deadline_at', 'label' => 'Zaman Sınırı', 'class' => 'w-64'],
            ['key' => 'updatedBy.name', 'label' => 'Satın Temsilcisi', 'class' => 'w-64'],
            ['key' => 'total', 'label' => 'Toplam Tutar'],
            ['key' => 'status', 'label' => 'Durum'],
            ['key' => 'actions', 'label' => 'Eylemler', 'sortable' => false],
        ];
    }

    public function proposals(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\Proposal::query()
            ->when($this->status, fn(Builder $q) => $q->whereIn('status', $this->status))
            ->when($this->search, fn(Builder $q) => $q->whereLike(['name', 'contact_name', 'proposal_no'], "%$this->search%"))
            ->when(!$this->include_archives, fn(Builder $q) => $q->whereNot('status', \App\Enums\Proposal\ProposalStatus::ARCHIVE))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function with(): array
    {
        return [
            'proposals' => $this->proposals(),
            'headers' => $this->headers(),
            'countries' => \App\Models\Country::all(),
            'statuses' => \App\Enums\Proposal\ProposalStatus::listForMaryUI(),
            'total_proposals' => \App\Models\Proposal::count(),
            'open_proposals' => \App\Models\Proposal::where('status', \App\Enums\Proposal\ProposalStatus::DRAFT)->count(),
            'accepted_proposals' => \App\Models\Proposal::where('status', \App\Enums\Proposal\ProposalStatus::APPROVED)->count(),
            'total_value' => \App\Models\Proposal::sum('total'),
        ];
    }
}; ?>

<div class="bg-gradient-to-br from-rose-50 via-rose-100 to-rose-200 min-h-screen p-6">
    <x-modal wire:model="showUnArchiveModal" title="Arşivden Çıkar">
        <div>Bu teklif arşivden çıkarılacak ve durumu taslak olarak güncellenecektir!</div>
        <x-slot:actions>
            <x-button label="Kapat" wire:click="$set('showUnArchiveModal',false)"/>
            <x-button label="Arşivden Çıkar" wire:click="unArchive" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <!-- HEADER -->
    <x-header title="Teklifler" separator progress-indicator class="mb-6">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable
                     class="w-64"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-toggle label="Arşiv dahil" wire:model.live="include_archives" right/>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"
                      class="bg-rose-500 hover:bg-rose-600 text-white"/>
            <x-button label="Yeni" link="/proposals/create" responsive icon="o-plus"
                      class="bg-rose-500 hover:bg-rose-600 text-white"/>
        </x-slot:actions>
    </x-header>

    <!-- STATS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat title="Toplam Teklif" value="{{ $total_proposals }}" icon="o-document-text" class="bg-rose-100"/>
        <x-stat title="Açık Teklifler" value="{{ $open_proposals }}" icon="o-clock" class="bg-yellow-100"/>
        <x-stat title="Kabul Edilen" value="{{ $accepted_proposals }}" icon="o-check-circle" class="bg-green-100"/>
        <x-stat title="Toplam Değer" value="{{ format_number($total_value, decimals: 0, symbol: '₺') }}"
                icon="o-currency-dollar" class="bg-blue-100"/>
    </div>

    <!-- QUICK FILTERS -->
    <div class="flex flex-wrap gap-2 mb-6">
        <x-button label="Tümü" wire:click="setStatusFilter(null)"
                  :class="$status === null ? 'bg-rose-500 text-white' : 'bg-gray-200'"/>
        <x-button label="Açık" wire:click="setStatusFilter(1)"
                  :class="in_array(1, $status ?? []) ? 'bg-rose-500 text-white' : 'bg-gray-200'"/>
        <x-button label="Kabul Edildi" wire:click="setStatusFilter(2)"
                  :class="in_array(2, $status ?? []) ? 'bg-rose-500 text-white' : 'bg-gray-200'"/>
        <x-button label="Reddedildi" wire:click="setStatusFilter(3)"
                  :class="in_array(3, $status ?? []) ? 'bg-rose-500 text-white' : 'bg-gray-200'"/>
    </div>

    <!-- TABLE  -->
    <x-card class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-4 bg-rose-50 border-b border-rose-100">
            <h3 class="text-lg font-semibold text-rose-800">Teklif Detayları</h3>
        </div>
        <x-table link="proposals/{id}/edit" striped with-pagination :headers="$headers" :rows="$proposals"
                 :sort-by="$sortBy">
            @php
                /** @var \App\Models\Proposal $proposal */
            @endphp

            @scope('cell_contact_name', $proposal)
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-rose-500" viewBox="0 0 20 20"
                     fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                          clip-rule="evenodd"/>
                </svg>
                {{ $proposal->getContactName() }}
            </div>
            @endscope

            @scope('cell_deadline_at', $proposal)
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-rose-500" viewBox="0 0 20 20"
                     fill="currentColor">
                    <path fill-rule="evenodd"
                          d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                          clip-rule="evenodd"/>
                </svg>
                {{ $proposal->deadline_at->format('d-m-Y') }}
            </div>
            @endscope

            @scope('cell_status', $proposal)
            <x-badge :value="$proposal->status->text()" class="badge-{{ $proposal->status->style() }}"/>
            @endscope

            @scope('cell_total', $proposal)
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-rose-500" viewBox="0 0 20 20"
                     fill="currentColor">
                    <path
                            d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                          clip-rule="evenodd"/>
                </svg>
                {{ format_number($proposal->total, decimals: 0, symbol: '₺') }}
            </div>
            @endscope

            @scope('actions', $proposal)
            <div class="flex items-center justify-end space-x-2">
                @if ($proposal->status->isArchive())
                    <x-button icon="o-arrow-path" size="sm" wire:click="unArchiveModal('{{ $proposal->id }}')"
                              tooltip="Arşivden Çıkar"
                              class="btn-ghost btn-sm text-green-500"/>
                @endif
            </div>
            @endscope

        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filtre" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-choices-offline style="height: 56px" searchable multiple wire:model.live.debounce.1000ms="status"
                               label="Durumu" :options="$statuses"/>
        </div>

        <x-slot:actions>
            <x-button label="Temizle" icon="o-x-mark" wire:click="clear" spinner
                      class="bg-gray-100 hover:bg-gray-200 text-gray-700"/>
            <x-button label="Tamam" icon="o-check" class="bg-rose-500 hover:bg-rose-600 text-white"
                      @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>

    <!-- DETAIL MODAL -->
    <x-modal wire:model="showDetailModal" title="Teklif Detayları">
        @if($selectedProposal)
            <div class="grid grid-cols-2 gap-4">
                <div><strong>Teklif No:</strong> {{ $selectedProposal->proposal_no }}</div>
                <div><strong>Müşteri:</strong> {{ $selectedProposal->contact_name }}</div>
                <div><strong>Durum:</strong> {{ $selectedProposal->status->text() }}</div>
                <div><strong>Toplam
                        Tutar:</strong> {{ format_number($selectedProposal->total, decimals: 0, symbol: '₺') }}</div>
                <div><strong>Açılış Tarihi:</strong> {{ $selectedProposal->created_at->format('d-m-Y') }}</div>
                <div><strong>Zaman Sınırı:</strong> {{ $selectedProposal->deadline_at->format('d-m-Y') }}</div>
                <div><strong>Satın Temsilcisi:</strong> {{ $selectedProposal->updatedBy->name }}</div>
            </div>
        @endif
    </x-modal>
</div>
