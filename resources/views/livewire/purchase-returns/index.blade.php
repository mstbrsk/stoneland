<?php

use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use \Livewire\WithPagination;

    public string $search = '';

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
            ['key' => 'purchase.supplier.name', 'label' => 'Tedarikçi'],
            ['key' => 'purchase.purchase_no', 'label' => 'Satın Alma No'],
            ['key' => 'updated_at', 'label' => 'Düzenleme Tarihi', 'class' => 'w-64'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'class' => 'w-64'],
            ['key' => 'purchase.quantity', 'label' => 'Toplam Miktar'],
            ['key' => 'return_quantity', 'label' => 'İade Miktarı'],
            ['key' => 'status', 'label' => 'Durum'],
        ];
    }

    public function purchaseReturns(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\PurchaseReturn::query()
            ->when(
                $this->search, fn(Builder $q) => $q->whereLike(['purchase.purchase_no', 'updatedBy.name', 'purchase.quantity'], "%$this->search%")
                ->orWhereRelation('sale.contact', 'name', 'ilike', "%$this->search%")
            )
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function with(): array
    {
        return [
            'purchaseReturns' => $this->purchaseReturns(),
            'headers' => $this->headers(),
        ];
    }
}; ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-gray-50">
    <!-- HEADER -->
    <div class="mb-8 bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h1 class="text-3xl font-bold text-gray-800">Satınalma İade</h1>
            <div class="flex items-center space-x-4">
                <x-input placeholder="Müşteri veya Satış No ile ara..." wire:model.live.debounce="search"

                         icon="o-magnifying-glass" class="w-64 bg-gray-100 border-0 focus:ring-2 focus:ring-blue-500"/>
                <x-button label="Filtrele" @click="$wire.drawer = true" responsive icon="o-funnel"
                          class="btn-primary hover:scale-105 transition-transform duration-200"/>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if(count($purchaseReturns)===0)
            <div class="flex flex-col items-center justify-center py-12 bg-gray-50 rounded-lg shadow-sm">
                <img src="{{ asset('/assets/images/no-data.png') }}" alt="Kayıt bulunamadı" class="w-48 h-48 mb-4">

                <h3 class="text-xl font-semibold text-gray-700 mb-2">Kayıt Bulunamadı</h3>
                <p class="text-gray-500 text-center max-w-md">
                    Henüz hiç satın alma iadesi bulunmuyor
                </p>
            </div>
        @else
            <x-table link="/purchase-returns/{purchase_id}/edit" hover with-pagination :headers="$headers"
                     :rows="$purchaseReturns" :sort-by="$sortBy" class="w-full">
                @php
                    /** @var \App\Models\PurchaseReturn $purchaseReturn */
                @endphp
                @scope('cell_sale.contact.name', $purchaseReturn)
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                        {{ strtoupper(substr($purchaseReturn->purchase->supplier->name, 0, 1)) }}
                    </div>
                    <span>{{ $purchaseReturn->purchase?->supplier?->name }}</span>
                </div>
                @endscope

                @scope('cell_deadline_at', $purchaseReturn)
                <span class="text-sm">{{ $purchaseReturn->created_at->format('d.m.Y') }}</span>
                @endscope

                @scope('cell_return_quantity', $purchaseReturn)
                <span
                    class="font-medium">{{ collect($purchaseReturn->returnQty())->sum(fn(array $data)=>(int)$data['qty'] ) }}</span>
                @endscope

                @scope('cell_status', $purchaseReturn)
                <span class="text-gray-800">{!! $purchaseReturn->status->textWithBadge() !!}</span>
                @endscope

                @scope('actions', $purchaseReturn)
                <div class="flex items-center justify-end space-x-2">
                    @if ($purchaseReturn->status->isDone())
                        <x-button icon="o-eye" tooltip="Detayları Görüntüle"
                                  link="/purchase-returns/{{ $purchaseReturn->purchase_id }}/edit"
                                  class="btn-ghost btn-sm hover:bg-gray-100 hover:scale-110 transition-transform duration-200"/>
                    @else
                        <x-button icon="o-pencil-square" tooltip="Düzenle"
                                  link="/purchase-returns/{{ $purchaseReturn->purchase_id }}/edit"
                                  class="btn-ghost btn-sm hover:bg-gray-100 hover:scale-110 transition-transform duration-200"/>
                    @endif
                </div>
                @endscope
            </x-table>
        @endif
    </div>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Gelişmiş Filtre" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-6">
            <x-input label="Müşteri Adı" wire:model="filterCustomer" placeholder="Müşteri adı ile filtrele"
                     icon="o-user"/>
            <x-input label="Satış No" wire:model="filterSalesNo" placeholder="Satış numarası ile filtrele"
                     icon="o-hashtag"/>
            <x-select label="Durum" wire:model="filterStatus" placeholder="Duruma göre filtrele" icon="o-flag">
                <option value="">Tümü</option>
                <!-- Durum seçeneklerini ekleyin -->
            </x-select>
        </div>

        <x-slot:actions>
            <x-button label="Sıfırla" icon="o-arrow-path" wire:click="clear" spinner
                      class="btn-outline hover:bg-red-50 hover:text-red-500 transition-colors duration-200"/>
            <x-button label="Uygula" icon="o-check"
                      class="btn-primary hover:scale-105 transition-transform duration-200"
                      @click="$wire.drawer = false" spinner/>
        </x-slot:actions>
    </x-drawer>
</div>
