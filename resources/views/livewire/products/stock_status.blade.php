<?php

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

    public bool $showStockDistributionModal = false;

    public ?\App\Models\Product $selectedProduct = null;

    public ?string $variantName = null;

    public ?Collection $productVariants = null;

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
            ['key' => 'stock_code', 'label' => 'Stok Kodu', 'class' => 'w-24 lg:w-32'],
            ['key' => 'name', 'label' => 'Stok Adı', 'class' => 'w-32 lg:w-48'],
            ['key' => 'warehouse.name', 'label' => 'Depo Yeri', 'class' => 'w-24 lg:w-32 hidden md:table-cell'],
            ['key' => 'stock_count', 'label' => 'Miktar', 'class' => 'w-16'],
            ['key' => 'unit.name', 'label' => 'Birim', 'class' => 'w-16'],
            ['key' => 'sales_price', 'label' => 'Fiyat', 'class' => 'w-20 hidden lg:table-cell'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'class' => 'w-24 lg:w-32 hidden md:table-cell', 'sortable' => false],
        ];
    }

    public function showStockDistribution(\App\Models\Product $product): void
    {
        $this->showStockDistributionModal = true;

        $this->selectedProduct = $product;

        $productVariants = \App\Models\ProductVariant::query()
            ->orderByDesc('stock')
            ->where('product_id', $this->selectedProduct->id)
            ->get();

        $this->productVariants = $productVariants->map(function (ProductVariant $productVariant) {
            return [
                'name' => $productVariant->getVariantName(),
                'qty' => $productVariant->stock,
            ];
        })
            ->sortBy('name');

        $this->variantName =$productVariants[0]->getAttributeName();
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
    <x-header title="Stok Durumu" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"/>
            <x-button label="Yeni" link="/products/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- SUMMARY SECTION -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat title="Toplam Ürün" :value="$products->total()" icon="o-cube"/>
            <x-stat title="Toplam Stok Miktarı"
                    :value="$products->sum(fn(\App\Models\Product $product)=> $product->stockCount())" icon="o-scale"/>
            <x-stat title="Toplam Depo Sayısı" :value="$warehouses->count()" icon="o-building-office-2"/>
        </div>
    </div>

    <!-- TABLE  -->
    <x-card class="overflow-x-auto">
        <x-table with-pagination :headers="$headers" :rows="$products" :sort-by="$sortBy" class="w-full">
            @php
                /** @var \App\Models\Product $product */
            @endphp
            @scope('cell_stock_count',$product)
            {{ $product->stockCount() }}
            @endscope

            @scope('actions', $product)
            <div class="flex space-x-2">
                <x-button tooltip="Stok Dağılımı" icon="o-document-magnifying-glass"
                          wire:click.prevent="showStockDistribution('{{ $product->id }}')"
                          spinner class="btn-outline"/>
            </div>
            @endscope
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


    @if ($showStockDistributionModal)
        <x-mary-modal wire:model.live="showStockDistributionModal" title="Stok Dağılımı" max-width="4xl">
            <div class="p-6 space-y-6">
                <h2 class="text font-bold text-gray-800 mb-4 flex items-center">
                    <x-mary-icon name="o-cube" class="w-6 h-6 mr-2 text-indigo-600"/>
                    {{ $selectedProduct->name }}
                </h2>

                <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                    <div
                        class="grid grid-cols-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold">
                        <div class="p-4 text-center flex items-center justify-center">
                            <x-mary-icon name="o-tag" class="w-5 h-5 mr-2"/>
                            {{ $variantName }}
                        </div>
                        <div class="p-4 text-center flex items-center justify-center">
                            <x-mary-icon name="o-chart-bar" class="w-5 h-5 mr-2"/>
                            Stok Miktarı
                        </div>
                    </div>

                    <div class="max-h-96 overflow-y-auto">
                        @php
                            /** @var \App\Models\ProductVariant[] $productVariants */
                        @endphp
                        @foreach($productVariants as $index => $variant)
                            <div
                                class="grid grid-cols-2 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition duration-150 ease-in-out">
                                <div class="p-4 text-center border-r border-gray-200 flex items-center justify-center">
                                    <x-mary-icon name="o-tag" class="w-4 h-4 mr-2 text-gray-500"/>
                                    {{ $variant['name'] }}
                                </div>
                                <div class="p-4 text-center font-medium flex items-center justify-center">
                                    <x-mary-icon name="{{ $variant['qty'] > 0 ? 'o-check-circle' : 'o-x-circle' }}"
                                                 class="w-5 h-5 mr-2 {{ $variant['qty'] > 0 ? 'text-green-500' : 'text-red-500' }}"/>
                                    <span class="{{ $variant['qty'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $variant['qty'] ?? 0 }}
                            </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button label="Kapat"
                               icon="o-x-mark"
                               class="bg-indigo-600 hover:bg-indigo-700 text-white"
                               x-on:click="$wire.showStockDistributionModal = false"/>
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>
