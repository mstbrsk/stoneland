<?php

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use \Livewire\WithPagination;

    public string $search = '';

    public int $country_id = 0;

    public bool $drawer = false;

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    // Reset pagination when any component property changes
    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
        }
    }

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    public function delete(Warehouse $warehouse): void
    {
        if (\App\Models\Product::where('warehouse_id', $warehouse->id)->exists()) {
            throw \Mary\Exceptions\ToastException::error('Bu depo kullanımda, silinemez!');
        }

        $warehouse->delete();

        $this->success("$warehouse->name silindi");
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Depo Adı', 'class' => 'w-64'],
            ['key' => 'short_name', 'label' => 'Kısa Ad', 'class' => 'w-64'],
            ['key' => 'address.name', 'label' => 'Adresi', 'class' => 'w-64', 'sortable' => false],
            ['key' => 'color', 'label' => 'Renk', 'class' => 'w-64', 'sortable' => false],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
        ];
    }

    public function warehouses(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Warehouse::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));;
    }

    public function with(): array
    {
        return [
            'warehouses' => $this->warehouses(),
            'headers' => $this->headers(),
        ];
    }
}; ?>
<div class="bg-gray-100 min-h-screen">
    <!-- HEADER -->
    <x-header title="Depolar" separator progress-indicator class="bg-white shadow-sm">
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.300ms="search" clearable
                     icon="o-magnifying-glass" class="max-w-sm"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtreler" @click="$wire.drawer = true" responsive
                      icon="o-adjustments-horizontal" class="btn-outline"/>
            <x-button label="Yeni Depo" link="/warehouses/create" responsive icon="o-plus"
                      class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <div class="container mx-auto px-4 py-8">
        <x-card class="bg-white shadow-md rounded-lg overflow-hidden">
            <x-table link="warehouses/{id}/edit" with-pagination :headers="$headers" :rows="$warehouses"
                     :sort-by="$sortBy" class="w-full">
                @scope('actions', $warehouse)
                <div class="flex space-x-2">
                    <x-button tooltip="Depo Stok Hareketleri" icon="o-document-magnifying-glass"
                              link="/product-transactions?warehouse_id={{ $warehouse->id }}"
                              spinner class="btn-ghost btn-sm"/>

                    <x-button tooltip="Depo Stok" icon="o-clipboard-document-list"
                              link="/products?warehouse_id={{ $warehouse->id }}"
                              spinner class="btn-ghost btn-sm"/>

                    <x-button icon="o-trash" wire:click="delete({{ $warehouse['id'] }})"
                              wire:confirm="Emin misiniz?" spinner
                              class="btn-ghost btn-sm text-red-500"/>
                </div>
                @endscope

                @scope('cell_color',$warehouse)
                <span class="inline-block w-6 h-6 rounded-full"
                      style="background-color: {{ $warehouse->color }}"></span>
                @endscope
            </x-table>
        </x-card>
    </div>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filtreler" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-6 p-4">
            <x-input placeholder="Ara..." wire:model.live.debounce.300ms="search" clearable
                     icon="o-magnifying-glass" class="w-full"/>
            <!-- Buraya daha fazla filtre seçeneği ekleyebilirsiniz -->
        </div>

        <x-slot:actions>
            <x-button label="Sıfırla" icon="o-x-mark" wire:click="clear" spinner class="btn-outline"/>
            <x-button label="Uygula" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
