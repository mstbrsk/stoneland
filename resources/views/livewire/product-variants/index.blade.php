<?php
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';
    public bool $drawer = false;
    public string $stock_code = '';
    public string $product_name = '';
    public array $filterWarehouses = [];
    public array $filterUsers = [];
    public array $sortBy = ['column' => 'attribute_items', 'direction' => 'asc'];
    public array $stock = ['qty' => 0];
    public  $page = 1;
    public function mount()
    {
        if (request()->filled('stock_code')) {
            $this->stock_code = request('stock_code');
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'stock_code', 'product_name', 'filterWarehouses', 'filterUsers', 'sortBy'])) {
            $this->resetPage();
        }
    }


    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filtre temizlendi');
    }
    public function nextPage()
    {
        $this->page++;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function gotoPage($page)
    {
        $this->page = $page;
    }
    public function updatedStock($name, $value)
    {
        $variantId = $value;
        $qty = $name['qty'];

        \App\Models\ProductVariant::findOrFail($variantId)->update([
            'stock' => $qty
        ]);

        $this->info('Adet güncellendi');
    }

    public function headers(): array
    {
        return [
            ['key' => 'product_name', 'label' => 'Ürün', 'class' => 'w-64'],
            ['key' => 'stock_code', 'label' => 'Stok Kodu', 'class' => 'w-64'],
            ['key' => 'attribute_items', 'label' => 'Varyant', 'class' => 'w-64', 'sortable' => true],
            ['key' => 'stock', 'label' => 'Depo - Stok Miktarı', 'class' => 'w-64'],
            ['key' => 'qty', 'label' => 'Varyant Stok Miktarı', 'class' => 'w-48'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
        ];
    }

    public function productVariants(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = \App\Models\ProductVariant::query()
            ->with('product');

        if ($this->search) {
            $query->whereLike(['product_name', 'stock_code', 'attribute_items', 'updatedBy.name'], $this->search);
        }

        if ($this->stock_code) {
            $query->whereHas('product', fn(Builder|\App\Models\Product $q) => $q->whereLike('stock_code', $this->stock_code));
        }

        if ($this->product_name) {
            $query->whereRaw('CAST(product_name AS UNSIGNED) = ?', [(int)$this->product_name]);
        }

        if ($this->filterWarehouses) {
            $query->whereHas('product.warehouse', fn(Builder|\App\Models\Warehouse $q) => $q->whereIn('id', $this->filterWarehouses));
        }

        if ($this->filterUsers) {
            $query->where(function (Builder $q) {
                $q->orWhereIn('created_by', $this->filterUsers)
                    ->orWhereIn('updated_by', $this->filterUsers);
            });
        }

        $allResults = $query->get();


        $groupedAndSortedResults = $allResults->groupBy('product_id')
            ->map(function ($group) {
                return $group->sortBy(function ($variant) {
                    return $variant->getVariantName();
                }, SORT_NATURAL | SORT_FLAG_CASE)
                    ->sortByDesc('created_at');
            })
            ->sortBy(function ($group) {
                return $group->first()->product->name;
            });
        $flattenedResults = $groupedAndSortedResults->flatten(1);


        $perPage = config('sap.pagination.per_page');
        $page = $this->page ?: 1;

        // Manuel sayfalama
        $items = $flattenedResults->forPage($page, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $flattenedResults->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }


    public function with(): array
    {
        return [
            'productVariants' => $this->productVariants(),
            'headers' => $this->headers(),
            'warehouses' => \App\Models\Warehouse::all(),
            'users' => \App\Models\User::all(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Ürün Varyantları" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table with-pagination  :headers="$headers" :rows="$productVariants" :sort-by="$sortBy">
            @php
                /** @var \App\Models\ProductVariant $productVariant */
            @endphp

            @scope('cell_attribute_items', $productVariant)
            {{ $productVariant->getVariantName() }}
            @endscope

            @scope('cell_stock_code', $productVariant)
            {{ $productVariant->product->stock_code }}
            @endscope

            @scope('cell_stock', $productVariant)
            @foreach($productVariant->getWarehouseStocks() as $stock)
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-small bg-gradient-to-r from-green-400 to-green-500 text-white shadow-sm transition-all duration-300 ease-in-out hover:from-green-500 hover:to-green-600 hover:shadow-md">
    {!! $stock !!}
</span>
            @endforeach
            @endscope

            @scope('cell_qty', $productVariant)
            <p class="text-center"> {{ $productVariant->stock }} </p>
            @endscope

        </x-table>
        <!-- Özelleştirilmiş sayfalama görünümü: resources/views/vendor/pagination/tailwind.blade.php -->
        @if ($productVariants->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-between">
        <span>
            @if ($productVariants->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button wire:click="previousPage('page')" wire:loading.attr="disabled" rel="prev" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    {!! __('pagination.previous') !!}
                </button>
            @endif
        </span>

                <span>
            @if ($productVariants->hasMorePages())
                        <button wire:click="nextPage('page')" wire:loading.attr="disabled" rel="next" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    {!! __('pagination.next') !!}
                </button>
                    @else
                        <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                    {!! __('pagination.next') !!}
                </span>
                    @endif
        </span>
            </nav>
        @endif




    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filtre" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input label="Stok Adı" wire:model.live.debounce.1000ms="product_name" icon="o-flag"/>

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
