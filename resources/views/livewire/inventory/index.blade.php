<?php

use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use \Livewire\WithPagination;

    public string $search = '';

    public bool $drawer = false;
    public bool $filtered = false;

    // Searchable fields
    public string $stock_code = '';
    public string $product_name = '';
    public array $filterWarehouses = [];
    public array $filterUsers = [];
    public array $filterProducts = [];
    public array $filterVariants = [];

    public ?array $variants = [];

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function mount()
    {
        if (request()->filled('stock_code')) {
            $this->stock_code = request('stock_code');
        }
    }

    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
        }

        $this->checkFilter();
    }

    public function checkFilter()
    {
        if (!empty($this->filterProducts) || !empty($this->filterUsers) || !empty($this->filterWarehouses)
            || !empty($this->filterVariants)) {
            $this->filtered = true;
        }

        if (empty($this->filterProducts) && empty($this->filterUsers) && empty($this->filterWarehouses)
            && empty($this->filterVariants)) {
            $this->filtered = false;
        }
    }

    public function updatedFilterProducts()
    {
        $this->variants = \App\Models\ProductVariant::whereIn('product_id', $this->filterProducts)
            ->get()
            ->map(fn(\App\Models\ProductVariant $variant) => [
                'id' => $variant->id,
                'name' => $variant->getVariantName(withProductName: true),
            ])
            ->toArray();
    }

    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filtre temizlendi');
    }

    public function headers(): array
    {
        return [
            ['key' => 'warehouse.name', 'label' => 'Depo Yeri', 'class' => 'w-64'],
            ['key' => 'product.name', 'label' => 'Ürün', 'class' => 'w-64'],
            ['key' => 'stock_code', 'label' => 'Stok Kodu', 'class' => 'w-64'],
            ['key' => 'variant.name', 'label' => 'Varyant', 'class' => 'w-64'],
            ['key' => 'quantity', 'label' => 'Stok Miktarı', 'class' => 'w-64'],
            ['key' => 'product.unit_id', 'label' => 'Birim', 'class' => 'w-64'],
            ['key' => 'stock_cost', 'label' => 'Depo Stok Maliyeti', 'class' => 'w-64'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
        ];
    }

    public function inventory(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\Inventory::query()
            ->with('variant', 'product', 'variant.product')
            ->when($this->search, fn(Builder $q) => $q->whereLike(['product_name', 'stock_code', 'attribute_items', 'stock_count', 'updatedBy.name'], $this->search))
            ->when(!empty($this->filterWarehouses), fn(Builder $q) => $q->whereIn('warehouse_id', $this->filterWarehouses))
            ->when($this->filterProducts, fn(Builder $q) => $q->whereIn('product_id', $this->filterProducts))
            ->when($this->filterVariants, fn(Builder $q) => $q->whereIn('variant_id', $this->filterVariants))
            ->when($this->filterUsers, fn(Builder $q) => $q->where(function (Builder $q) {
                $q->orWhereIn('created_by', $this->filterUsers)
                    ->orWhereIn('updated_by', $this->filterUsers);
            }))
            ->groupBy('variant_id', 'warehouse_id', 'id')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function with(): array
    {
        return [
            'productVariants' => $this->inventory(),
            'headers' => $this->headers(),
            'warehouses' => \App\Models\Warehouse::all(),
            'users' => \App\Models\User::all(),
            'products' => \App\Models\Product::all(),
        ];
    }
}; ?>

<div class=" min-h-screen">
    <!-- HEADER -->
    <x-header title="Depo Stokları/Envanter" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"
                     class="w-64"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"
                      :class="$filtered ? 'btn-warning' : 'btn-outline'"
            />
        </x-slot:actions>
    </x-header>

    <!-- SUMMARY SECTION -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat title="Toplam Ürün Çeşidi" :value="$productVariants->unique('product_id')->count()" icon="o-cube"/>
            <x-stat title="Toplam Varyant" :value="$productVariants->count()" icon="o-squares-2x2"/>
            <x-stat title="Toplam Stok Miktarı" :value="$productVariants->sum('quantity')" icon="o-scale"/>
        </div>
    </div>

    <!-- TABLE  -->
    <x-card class="bg-white shadow-sm">
        <x-table with-pagination :headers="$headers" :rows="$productVariants" :sort-by="$sortBy" class="table-hover">
            @php
                /** @var \App\Models\Inventory $productVariant */
            @endphp

            @scope('cell_variant.name', $productVariant)
            <span class="font-medium">{{ $productVariant->variant?->getVariantName() }}</span>
            @endscope

            @scope('cell_stock_code', $productVariant)
            <span class="text-gray-600">{{ $productVariant->variant?->product?->stock_code }}</span>
            @endscope

            @scope('cell_warehouse.name', $productVariant)
            {!! $productVariant->warehouse->textWithColor() !!}
            @endscope

            @scope('cell_quantity', $productVariant)
            <span class="font-semibold {{ $productVariant->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $productVariant->quantity }}
            </span>
            @endscope

            @scope('cell_product.unit_id', $productVariant)
            <span class="font-semibold {{ $productVariant->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $productVariant->product->unit->name }}
            </span>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filtreler" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-choices-offline label="Depo Yeri" multiple wire:model.live.debounce="filterWarehouses"
                               :options="$warehouses"
                               icon="o-building-office"/>

            <x-choices-offline label="Kullanıcı" multiple wire:model.live.debounce="filterUsers"
                               :options="$users"
                               icon="o-user"/>

            <x-choices-offline label="Ürün" multiple wire:model.live.debounce.1000ms="filterProducts"
                               :options="$products"
                               icon="o-cube"/>

            <x-choices-offline label="Varyantlar" multiple wire:model.live.debounce.1000ms="filterVariants"
                               :options="$variants"
                               icon="o-squares-2x2"/>
        </div>

        <x-slot:actions>
            <x-button label="Temizle" icon="o-trash" wire:click="clear" spinner class="btn-outline"/>
            <x-button label="Uygula" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
