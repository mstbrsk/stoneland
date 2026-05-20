<?php

use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use \Livewire\WithPagination;

    public string $search = '';

    // Searchable fields
    public ?int $warehouse_id = null;

    public bool $drawer = false;

    public string $stock_code = '';
    public string $name = '';
    public array $filterWarehouses = [];
    public array $filterUsers = [];

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function mount()
    {
        if (request()->filled('warehouse_id')) {
            $this->warehouse_id = (int)request('warehouse_id');
        }
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
        $this->success('Filtre temizlendi');
    }

    public function delete(\App\Models\Product $product): void
    {
        if (!check_permission('delete_product_cart')) {
            throw \Mary\Exceptions\ToastException::error('Silme işlemi için yetkiniz yok!');
        }

        // Todo: check other usage
        if (\App\Models\PurchaseItem::where('product_id', $product->id)->exists()) {
            throw \Mary\Exceptions\ToastException::error('Bu ürün kullanımda, silinemez!');
        }

        $product->variants()->each(fn(\App\Models\ProductVariant $variant) => $variant->delete());
        $product->delete();

        $this->success("{$product->name} adlı ürün silindi");
    }

    public function headers(): array
    {
        return [
            ['key' => 'stock_code', 'label' => 'Stok Kodu', 'class' => 'w-64'],
            ['key' => 'name', 'label' => 'Stok Adı', 'class' => 'w-64'],
            ['key' => 'warehouse.name', 'label' => 'Depo Yeri', 'class' => 'w-64'],
            ['key' => 'stock_count', 'label' => 'Miktar', 'class' => 'w-20'],
            ['key' => 'unit.name', 'label' => 'Birim', 'class' => 'w-20'],
            ['key' => 'sales_price', 'label' => 'Fiyat', 'class' => 'hidden lg:table-cell'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
        ];
    }

    public function products(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\Product::query()
            ->when($this->search, fn(Builder $q) => $q->whereLike(['name', 'stock_code', 'sales_price', 'warehouse.name', 'updatedBy.name'], $this->search))
            ->when($this->stock_code, fn(Builder $q) => $q->where('stock_code', 'like', "%{$this->stock_code}%"))
            ->when($this->warehouse_id, fn(Builder $q) => $q->where('warehouse_id', $this->warehouse_id))
            ->when($this->name, fn(Builder $q) => $q->where('name', 'like', "%{$this->name}%"))
            ->when($this->filterWarehouses, fn(Builder $q) => $q->whereIn('warehouse_id', $this->filterWarehouses))
            ->when($this->filterUsers, fn(Builder $q) => $q->where(function (Builder $q) {
                $q->orWhereIn('created_by', $this->filterUsers)
                    ->orWhereIn('updated_by', $this->filterUsers);
            }))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));;
    }

    public function with(): array
    {
        return [
            'products' => $this->products(),
            'headers' => $this->headers(),
            'countries' => \App\Models\Country::all(),
            'warehouses' => \App\Models\Warehouse::all(),
            'users' => \App\Models\User::all(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Stoklar" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"/>
            <x-button label="Yeni" link="/products/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table link="products/{id}/edit" with-pagination :headers="$headers" :rows="$products" :sort-by="$sortBy">
            @php
                /** @var \App\Models\Product $product */
            @endphp
            @scope('cell_stock_count',$product)
            {{ $product->stockCount() }}
            @endscope

            @scope('actions', $product)
            <div class="flex space-x-2">
                <x-button tooltip="Stok Hareketleri" icon="o-document-magnifying-glass"
                          link="/product-transactions?stock_code={{ $product['stock_code'] }}"
                          spinner class="btn-ghost btn-sm"/>

                @if ($product->hasVariant())
                    <x-button tooltip="Ürün Varyantları" icon="o-document-duplicate"
                              link="/product-variants?stock_code={{ $product['stock_code'] }}"
                              spinner class="btn-ghost btn-sm"/>
                @endif

                <x-button tooltip="Sil" icon="o-trash" wire:click="delete('{{ $product["id"] }}')" wire:confirm="Emin misiniz?"
                          spinner
                          class="btn-ghost btn-sm text-red-500"/>
                @endscope
            </div>
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input label="Stok Adı" wire:model.live.debounce.1000ms="name" icon="o-flag"/>

            <x-input label='Stok Kodu' wire:model.live.debounce.1000ms="stock_code" icon="o-flag"/>

            <x-choices-offline label="Depo Yeri" multiple wire:model.live.debounce.1000ms="filterWarehouses"
                               :options="$warehouses"
                               icon="o-home"/>

            <x-choices-offline label="Kullanıcı" multiple wire:model.live.debounce.1000ms="filterUsers"
                               :options="$users"
                               icon="o-user"/>
        </div>

        <x-slot:actions>
            <x-button label="Temizle" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Kapat" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
