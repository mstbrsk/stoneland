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

    public array $expanded = [];

    // Searchable fields
    public string $stock_code = '';
    public ?int $warehouse_id = null;

    public string $name = '';
    public array $filterFromWarehouses = [];
    public array $filterToWarehouses = [];
    public array $filterProducts = [];
    public array $filterVariants = [];
    public array $filterUsers = [];

    public ?array $variants = [];

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function mount()
    {
        if (request()->filled('stock_code')) {
            $this->stock_code = request('stock_code');
        }

        if (request()->filled('warehouse_id')) {
            $this->warehouse_id = (int)request('warehouse_id');
        }
    }

    public function expandAll()
    {
        $this->expanded = $this->products()->pluck('id')->toArray();
    }

    public function collapseAll()
    {
        $this->expanded = [];
    }

    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
        }

        $this->checkFilter();
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

    public function checkFilter()
    {
        if (!empty($this->filterProducts) || !empty($this->filterUsers) || !empty($this->filterToWarehouses)
            || !empty($this->filterFromWarehouses) || !empty($this->filterVariants)) {
            $this->filtered = true;
        }

        if (empty($this->filterProducts) && empty($this->filterUsers) && empty($this->filterToWarehouses)
            && empty($this->filterFromWarehouses) && empty($this->filterVariants)) {
            $this->filtered = false;
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
            ['key' => 'fromWarehouse.name', 'label' => 'Çıkış Depo', 'class' => 'w-64'],
            ['key' => 'toWarehouse.name', 'label' => 'Transfer Depo', 'class' => 'w-64'],
            ['key' => 'product.name', 'label' => 'Stok Adı', 'class' => 'w-64'],
            ['key' => 'variant.name', 'label' => 'Varyant', 'class' => 'w-64'],
            ['key' => 'qty', 'label' => 'Miktar', 'class' => 'w-64'],
            ['key' => 'created_at', 'label' => 'Tarih', 'class' => 'w-64'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
        ];
    }

    public function transfers(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\WarehouseTransfer::query()
            ->when($this->search, fn(Builder $q) => $q->whereLike(['name', 'stock_code', 'sales_price', 'warehouse.name', 'updatedBy.name'], $this->search))
            ->when($this->filterFromWarehouses, fn(Builder $q) => $q->whereIn('from', $this->filterFromWarehouses))
            ->when($this->filterToWarehouses, fn(Builder $q) => $q->whereIn('to', $this->filterToWarehouses))
            ->when($this->filterProducts, fn(Builder $q) => $q->whereIn('product_id', $this->filterProducts))
            ->when($this->filterVariants, fn(Builder $q) => $q->whereIn('variant_id', $this->filterVariants))
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
            'transfers' => $this->transfers(),
            'headers' => $this->headers(),
            'warehouses' => \App\Models\Warehouse::all(),
            'products' => \App\Models\Product::all(),
            'users' => \App\Models\User::all(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Depo Transferleri" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"
                      :class="$filtered ? 'btn-warning' : ''"/>
            <x-button label="Yeni" link="/warehouse-transfers/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table with-pagination :headers="$headers" :rows="$transfers" wire:model="expanded" :sort-by="$sortBy">
            @php
                /** @var \App\Models\WarehouseTransfer $transfer */
            @endphp

            @scope('cell_variant.name',$transfer)
            {{ $transfer->variant->getVariantName() }}
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-choices-offline label="Çıkış Depo" multiple wire:model.live.debounce.1000ms="filterFromWarehouses"
                               :options="$warehouses"
                               icon="o-home"/>

            <x-choices-offline label="Transfer Depo" multiple wire:model.live.debounce.1000ms="filterToWarehouses"
                               :options="$warehouses"
                               icon="o-home"/>

            <x-choices-offline label="Ürün" multiple wire:model.live.debounce.1000ms="filterProducts"
                               :options="$products"
                               icon="o-home"/>

            <x-choices-offline label="Varyantlar" multiple wire:model.live.debounce.1000ms="filterVariants"
                               :options="$variants"
                               icon="o-home"/>
        </div>

        <x-slot:actions>
            <x-button label="Temizle" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Kapat" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
