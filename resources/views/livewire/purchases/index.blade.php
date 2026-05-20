<?php

use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use \Livewire\WithPagination;

    public string $search = '';
    public int $country_id = 0;
    public ?array $status = [];
    public array $filters = [
        'contact_name' => '',
    ];
    public bool $drawer = false;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

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
        $this->success('Filtre temizlendi.');
    }


    public function headers(): array
    {
        return [
            ['key' => 'supplier.name', 'label' => 'Tedarikçi', 'class' => 'w-64'],
            ['key' => 'purchase_no', 'label' => 'Sipariş No'],
            ['key' => 'products', 'label' => 'Ürünler', 'class' => 'w-64'],
            ['key' => 'quantity', 'label' => 'Toplam Miktar'],
            ['key' => 'warehouse.name', 'label' => 'Teslimat Yeri'],
            ['key' => 'purchased_at', 'label' => 'Sipariş Tarihi', 'class' => 'w-64'],
            ['key' => 'deadline_at', 'label' => 'Teslim Tarihi', 'class' => 'w-64'],
            ['key' => 'total', 'label' => 'Toplam Tutar'],
            ['key' => 'invoice_no', 'label' => 'Fatura No'],
            ['key' => 'status', 'label' => 'Durum'],
            ['key' => 'updatedBy.name', 'label' => 'Satın Alma Temsilcisi', 'class' => 'w-64'],
        ];
    }

    public function purchases(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\Purchase::query()
            ->with('items')
            ->when($this->status, fn(Builder $q) => $q->whereIn('status', $this->status))
            ->when($this->filters['contact_name'], fn(Builder $q) => $q->whereLike('supplier.name', $this->filters['contact_name']))
            ->when($this->search, fn(Builder $q) => $q->whereLike(['invoice_no', 'purchase_no', 'quantity', 'warehouse.name', 'total', 'updatedBy.name', 'supplier.name'], "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function getTotalOrdersProperty()
    {
        return \App\Models\Purchase::count();
    }

    public function getPendingOrdersProperty()
    {
        return \App\Models\Purchase::whereNotIn('status', [\App\Enums\Purchase\PurchaseStatus::IN_STOCK])->count();
    }

    public function getCompletedOrdersProperty()
    {
        return \App\Models\Purchase::where('status', \App\Enums\Purchase\PurchaseStatus::IN_STOCK)->count();
    }

    public function getTotalValueProperty()
    {
        return \App\Models\Purchase::sum('total');
    }

    public function with(): array
    {
        return [
            'purchases' => $this->purchases(),
            'headers' => $this->headers(),
            'countries' => \App\Models\Country::all(),
            'statuses' => \App\Enums\Purchase\PurchaseStatus::listForMaryUI(),
            'cellDecoration' => [
                'next_activity' => [
                    'bg-yellow-500/25' => fn(\App\Models\Purchase $purchase) => now()->diffInDays($purchase->deadline_at) < 0
                ]
            ],
        ];
    }
}; ?>


<div class="bg-gradient-to-br from-blue-50 via-blue-100 to-blue-200 min-h-screen p-6">
    <x-header title="Satın Alma Siparişleri" subtitle="Tedarik Analizi ve Yönetimi" separator progress-indicator
              class="mb-6">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Ara..." wire:model.live.debounce="search" class="w-64"/>
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-funnel" @click="$wire.drawer = true" class="bg-blue-500 hover:bg-blue-600 text-white"/>
            <x-button label="Yeni Sipariş" link="/purchases/create" icon="o-plus"
                      class="bg-blue-600 hover:bg-blue-700 text-white"/>
        </x-slot:actions>
    </x-header>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-4 bg-blue-50 border-b border-blue-100">
            <h3 class="text-lg font-semibold text-blue-800">Sipariş Detayları</h3>
        </div>

        <x-table link="purchases/{id}/edit" :headers="$headers" :rows="$purchases" :sort-by="$sortBy" with-pagination
                 class="w-full">
            @php
                /** @var \App\Models\Purchase $purchase */
            @endphp

            @scope('cell_supplier.name', $purchase)
            <div class="flex items-center">
                <div
                        class="w-10 h-10 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center mr-3 font-semibold">
                    {{ strtoupper(substr($purchase->supplier->name, 0, 1)) }}
                </div>
                <span class="font-medium">{{ $purchase->supplier->name }}</span>
            </div>
            @endscope

            @scope('cell_purchased_at', $purchase)
            <span class="text-sm text-gray-600">
                {{ $purchase->purchased_at->format('d M Y') }}
            </span>
            @endscope

            @scope('cell_deadline_at', $purchase)
            <span class="text-sm text-gray-600">
                {{ $purchase->deadline_at->format('d M Y') }}
            </span>
            @endscope

            @scope('cell_status', $purchase)
            {{ $purchase->status->text() }}
            @endscope

            @scope('cell_total', $purchase)
            <span class="text-sm font-medium text-gray-900">
                {{ format_number($purchase->total, decimals: 0, symbol: '₺') }}
            </span>
            @endscope

            @scope('actions', $purchase)
            <div class="flex items-center space-x-2">
                @if ($purchase->status->isInStock())
                    <x-button
                            icon="o-receipt-refund"
                            :tooltip="$purchase->hasReturn() ? 'İade Detayı' : 'İade Başlat'"
                            link="/purchase-returns/{{ $purchase->id }}/edit"
                            class="{{ $purchase->hasReturn() ? 'text-red-500 hover:text-red-700' : 'text-gray-600 hover:text-gray-800' }}"
                    />
                @endif
                <x-button
                        icon="o-pencil"
                        tooltip="Düzenle"
                        link="/purchases/{{ $purchase->id }}/edit"
                        class="text-blue-600 hover:text-blue-800"
                />
            </div>
            @endscope
        </x-table>
    </div>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filtre" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-6">
            <x-input label="Firma Adı" wire:model.live="filters.contact_name" class="w-full"/>
            <x-choices-offline
                    style="height: 56px"
                    searchable
                    multiple
                    wire:model.live.debounce.1000ms="status"
                    label="Durumu"
                    :options="$statuses"
            />
        </div>

        <x-slot:actions>
            <x-button
                    label="Temizle"
                    icon="o-x-mark"
                    wire:click="clear"
                    spinner
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300"
            />
            <x-button
                    label="Uygula"
                    icon="o-check"
                    class="bg-blue-600 text-white hover:bg-blue-700"
                    @click="$wire.drawer = false"
            />
        </x-slot:actions>
    </x-drawer>
</div>
