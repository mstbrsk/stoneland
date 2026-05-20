<?php

use App\Models\Shipment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public array $filters = [
        'sales_no' => '',
        'contact_name' => '',
        'status' => [],
        'start_date' => '',
        'end_date' => '',
    ];

    public function updating($name, $value): void
    {
        if ($name === 'search') {
            $this->resetPage();
        }
    }

    public function clear(): void
    {
        $this->reset(['search', 'sortBy']);
        $this->resetPage();
        $this->success('Filtre temizlendi.');
    }

    public function applyFilters()
    {
        $this->drawer = false;

        $this->shipments();
    }

    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'sale.sales_no', 'label' => 'Sipariş No' ],
            ['key' => 'contact.name', 'label' => 'Firma' ,  'class' => 'w-48'],
            ['key' => 'total_qty', 'label' => 'Toplam Miktar' , 'class' => 'w-30'],
            ['key' => 'shipped_qty', 'label' => 'Gönderilen Miktar'],
            ['key' => 'remain_qty', 'label' => 'Kalan Miktar'],
            ['key' => 'finished_at', 'label' => 'Sevkiyat Tarihi'],
            ['key' => 'status', 'label' => 'Durum'],
        ];
    }

    #[Computed]
    public function shipments(): LengthAwarePaginator
    {
        return Shipment::query()
            ->with(['sale', 'items'])
            ->when($this->search, fn(Builder $query) => $query->whereLike(['sale.sales_no', 'contact.name', 'sale.quantity'], $this->search))
            ->when($this->filters['sales_no'], fn(Builder $q) => $q->whereLike('sale.sales_no', $this->filters['sales_no']))
            ->when($this->filters['contact_name'], fn(Builder $q) => $q->whereLike('contact.name', $this->filters['contact_name']))
            ->when($this->filters['status'], fn(Builder $q) => $q->whereIn('status', $this->filters['status']))
            ->when($this->filters['start_date'], fn(Builder $q) => $q->whereDate('created_at', '>=', $this->filters['start_date']))
            ->when($this->filters['end_date'], fn(Builder $q) => $q->whereDate('created_at', '<=', $this->filters['end_date']))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(config('sap.pagination.per_page'));
    }
}; ?>
<div class="container mx-auto px-6 py-8">
    <!-- HEADER -->
    <x-header title="Sevkiyat Listesi" separator progress-indicator>
        <x-slot:middle class="flex justify-end items-center space-x-4">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass" class="w-full max-w-md rounded-full border-gray-300 focus:ring focus:ring-indigo-200"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel" class="rounded-full bg-indigo-600 text-white hover:bg-indigo-700"/>
        </x-slot:actions>
    </x-header>

    <!-- MAIN CONTENT -->
    <x-card class="mt-8 shadow-lg rounded-lg overflow-hidden">
        <x-table
            link="shipments/{id}"
            hover
            with-pagination
            :headers="$this->headers"
            :rows="$this->shipments"
            :sort-by="$sortBy"
            class="w-11/12"
        >
            @php
                /** @var Shipment $shipment */
            @endphp
            @scope('cell_sales_no', $shipment)
            <span class="font-semibold text-gray-800">{{ $shipment->sale->sales_no }}</span>
            @endscope

            @scope('cell_contact.name', $shipment)
            <span class="text-gray-600">{{ $shipment->contact->name }}</span>
            @endscope

            @scope('cell_total_qty', $shipment)
            <span class="font-semibold">{{ number_format($shipment->sale->quantity) }}</span>
            @endscope

            @scope('cell_shipped_qty', $shipment)
            <span class="font-semibold text-green-500">{{ number_format($shipment->items->sum('shipped_qty')) }}</span>
            @endscope

            @scope('cell_remain_qty', $shipment)
            <span class="font-semibold text-red-500">{{ number_format($shipment->sale->quantity - $shipment->items->sum('shipped_qty')) }}</span>
            @endscope

            @scope('cell_finished_at', $shipment)
            @if ($shipment->status->isShipped())
                <span class="font-semibold text-green-500">{{ $shipment->updated_at->format('d-m-Y H:i') }}</span>
            @else
                <span>-</span>
            @endif
            @endscope

            @scope('cell_status', $shipment)
            {!! $shipment->status->textWithBadge() !!}
            @endscope

            @scope('actions', $shipment)
            <x-button
                icon="o-eye"
                link="shipments/{{ $shipment->id }}"
                class="btn-ghost btn-sm text-indigo-600 hover:text-indigo-900"
                tooltip="Detayları Görüntüle"
            />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer
        wire:model="drawer"
        title="Gelişmiş Filtre"
        right
        separator
        with-close-button
        class="lg:w-1/3 bg-white shadow-xl"
    >
        <div class="space-y-6 px-6 py-8">
            <x-input label="Sipariş No" wire:model.live.debounce="filters.sales_no" class="rounded-lg"/>
            <x-input label="Firma Adı" wire:model.live="filters.contact_name" class="rounded-lg"/>
            <x-choices-offline
                label="Durum"
                wire:model.live="filters.status"
                multiple
                searchable
                :options="\App\Enums\Shipment\ShipmentStatus::listForMaryUI()"
                class="rounded-lg"
            />
            <x-datetime label="Başlangıç Tarihi" wire:model.live="filters.start_date" class="rounded-lg"/>
            <x-datetime label="Bitiş Tarihi" wire:model.live="filters.end_date" class="rounded-lg"/>
        </div>

        <x-slot:actions>
            <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
                <x-button
                    label="Temizle"
                    icon="o-x-mark"
                    wire:click="clear"
                    spinner
                    class="btn-outline bg-white text-gray-700 hover:bg-gray-100 rounded-full"
                />
                <x-button label="Kapat" icon="o-check" class="btn-primary bg-indigo-600 text-white hover:bg-indigo-700 rounded-full" @click="$wire.drawer = false"/>
            </div>
        </x-slot:actions>
    </x-drawer>
</div>

