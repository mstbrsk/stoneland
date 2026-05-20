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
    public bool $drawer = false;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public array $filters = [
        'contact_name' => '',
    ];

    // New properties for metrics
    public $totalSales;
    public $averageAmount;
    public $totalRevenue;

    public function mount(): void
    {
        $this->updateMetrics();
    }

    public function updateMetrics(): void
    {
        $this->totalSales = \App\Models\Sale::count();
        $this->averageAmount = \App\Models\Sale::avg('total');
        $this->totalRevenue = \App\Models\Sale::sum('total');
    }

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
            ['key' => 'contact.name', 'label' => 'Müşteri', 'class' => 'w-48'],
            ['key' => 'sales_no', 'label' => 'Satış No'],
            ['key' => 'deadline_at', 'label' => 'Teslim Tarihi', 'class' => 'w-30'],
            ['key' => 'updatedBy.name', 'label' => 'Temsilci', 'class' => 'w-48'],
            ['key' => 'total', 'label' => 'Toplam Tutar'],
            ['key' => 'deliveryAddress.name', 'label' => 'Teslimat Adresi'],
            ['key' => 'status', 'label' => 'Durum'],
        ];
    }

    public function sales(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\Sale::query()
            ->when($this->status, fn(Builder $q) => $q->whereIn('status', $this->status))
            ->when($this->filters['contact_name'], fn(Builder $q) => $q->whereLike('contact.name', $this->filters['contact_name']))
            ->when($this->search, fn(Builder $q) => $q->whereLike(['contact.name', 'sales_no', 'user.name'], $this->search))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function with(): array
    {
        return [
            'sales' => $this->sales(),
            'headers' => $this->headers(),
            'countries' => \App\Models\Country::all(),
            'statuses' => \App\Enums\Sale\SaleStatus::listForMaryUI(),
            'totalSales' => $this->totalSales,
            'averageAmount' => $this->averageAmount,
            'totalRevenue' => $this->totalRevenue,
        ];
    }
}; ?>

<div class="bg-gradient-to-br from-rose-50 via-rose-100 to-rose-200 min-h-screen p-6">
    <x-header title="Satış Siparişleri" subtitle="Satış Analizi ve Yönetimi" separator progress-indicator class="mb-6">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Ara..." wire:model.live.debounce="search" class="w-64"/>
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-funnel" @click="$wire.drawer = true" class="bg-rose-500 hover:bg-rose-600 text-white"/>
            <x-button label="Yeni Satış" link="/sales/create" icon="o-plus"
                      class="bg-rose-600 hover:bg-rose-700 text-white"/>
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-card class="bg-rose-100 border-rose-200">
            <div class="flex items-center">
                <div class="mr-4">
                    <x-icon name="o-shopping-cart" class="w-10 h-10 text-rose-500"/>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-rose-800">Toplam Satış</h3>
                    <p class="text-sm text-rose-600">Tüm zamanlar</p>
                    <span class="text-2xl font-bold text-rose-700">{{ number_format($totalSales) }}</span>
                </div>
            </div>
        </x-card>

        <x-card class="bg-amber-100 border-amber-200">
            <div class="flex items-center">
                <div class="mr-4">
                    <x-icon name="o-banknotes" class="w-10 h-10 text-amber-500"/>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-amber-800">Ortalama Tutar</h3>
                    <p class="text-sm text-amber-600">Satış başına</p>
                    <span class="text-2xl font-bold text-amber-700">{{ number_format($averageAmount, 2) }} ₺</span>
                </div>
            </div>
        </x-card>

        <x-card class="bg-emerald-100 border-emerald-200">
            <div class="flex items-center">
                <div class="mr-4">
                    <x-icon name="o-currency-dollar" class="w-10 h-10 text-emerald-500"/>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-emerald-800">Toplam Gelir</h3>
                    <p class="text-sm text-emerald-600">Tüm satışlar</p>
                    <span class="text-2xl font-bold text-emerald-700">{{ number_format($totalRevenue, 2) }} ₺</span>
                </div>
            </div>
        </x-card>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-4 bg-rose-50 border-b border-rose-100">
            <h3 class="text-lg font-semibold text-rose-800">Satış Detayları</h3>
        </div>

        <x-table link="/sales/{id}/edit" :headers="$headers" :rows="$sales" :sort-by="$sortBy" with-pagination
                 class="w-full">
            @php
                /** @var \App\Models\Sale $sale */
            @endphp

            @scope('cell_contact.name', $sale)
            <div class="flex items-center">
                <div
                    class="w-10 h-10 bg-rose-100 text-rose-700 rounded-full flex items-center justify-center mr-3 font-semibold">
                    {{ strtoupper(substr($sale->contact->name, 0, 1)) }}
                </div>
                <span class="font-medium">{{ $sale->contact->name }}</span>
            </div>
            @endscope

            @scope('cell_deadline_at', $sale)
            <span class="text-sm text-gray-600">
                {{ $sale->deadline_at->format('d M Y') }}
            </span>
            @endscope

            @scope('cell_status', $sale)
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
            {{ $sale->status->sold()
                ? 'bg-green-100 text-green-800'
                : 'bg-yellow-100 text-yellow-800'
            }}">
                {{ $sale->status->text() }}
            </span>
            @endscope

            @scope('cell_total', $sale)
            <span class="text-sm font-medium text-gray-900">
                {{ format_number($sale->total, decimals: 0, symbol: '₺') }}
            </span>
            @endscope

            @scope('actions', $sale)
            <div class="flex items-center space-x-2">
                @if ($sale->status->sold())
                    <x-button
                        icon="o-cube"
                        tooltip="Sevkiyat Detayı"
                        link="/shipments/{{ $sale->shipment_id }}"
                        class="text-blue-600 hover:text-blue-800"
                    />
                    <x-button
                        icon="o-receipt-refund"
                        :tooltip="$sale->hasReturn() ? 'İade Detayı' : 'İade Başlat'"
                        link="/sale-returns/{{ $sale->id }}/edit"
                        class="{{ $sale->hasReturn() ? 'text-red-500 hover:text-red-700' : 'text-gray-600 hover:text-gray-800' }}"
                    />
                @endif
                <x-button
                    icon="o-pencil"
                    tooltip="Düzenle"
                    link="/sales/{{ $sale->id }}/edit"
                    class="text-rose-600 hover:text-rose-800"
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
                class="bg-rose-600 text-white hover:bg-rose-700"
                @click="$wire.drawer = false"
            />
        </x-slot:actions>
    </x-drawer>
</div>
