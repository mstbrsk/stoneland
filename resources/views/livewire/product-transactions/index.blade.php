<?php

use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use \Livewire\WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $expanded = [];

    // Searchable fields
    public string $stock_code = '';
    public ?int $warehouse_id = null;

    public string $name = '';
    public array $filterWarehouses = [];
    public array $filterUsers = [];

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
            ['key' => 'type', 'label' => 'Hareket Türü', 'class' => 'w-64'],
            ['key' => 'relation_type', 'label' => 'İşlem Türü', 'class' => 'w-64'],
            ['key' => 'supplier_or_contact_name', 'label' => 'Tedarikçi/Müşteri', 'class' => 'w-64'],
            ['key' => 'product.stock_code', 'label' => 'Stok Kodu', 'class' => 'w-64'],
            ['key' => 'product.name', 'label' => 'Stok Adı', 'class' => 'w-64'],
            ['key' => 'warehouse.name', 'label' => 'Depo Yeri', 'class' => 'w-64'],
            ['key' => 'quantity', 'label' => 'Depo Miktarı', 'class' => 'w-64'],
            ['key' => 'product.unit.name', 'label' => 'Birim', 'class' => 'w-64'],
            ['key' => 'created_at', 'label' => 'Hareket Tarihi', 'class' => 'w-64'],
            ['key' => 'createdBy.name', 'label' => 'Kullanıcı'],
        ];
    }

    public function products(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\ProductTransaction::query()
            ->when($this->search, fn(Builder $q) => $q->whereLike(['name', 'stock_code', 'sales_price', 'warehouse.name', 'updatedBy.name'], $this->search))
            ->when($this->stock_code, fn(Builder $q) => $q->whereHas('product', fn(Builder|\App\Models\Product $q) => $q->whereLike('stock_code', $this->stock_code)))
            ->when(request()->filled('warehouse_id'), fn(Builder $q) => $q->where('warehouse_id', request('warehouse_id')))
            ->when($this->name, fn(Builder $q) => $q->where('name', 'like', "%{$this->name}%"))
            ->when($this->filterWarehouses, fn(Builder $q) => $q->whereIn('warehouse_id', $this->filterWarehouses))
            ->when($this->filterUsers, fn(Builder $q) => $q->where(function (Builder $q) {
                $q->orWhereIn('created_by', $this->filterUsers)
                    ->orWhereIn('updated_by', $this->filterUsers);
            }))
            ->select('relation_id', DB::raw('sum(quantity) as total_quantity'), 'type', 'relation_type', 'product_id', 'warehouse_id', 'created_at', 'created_by')
            ->groupBy('relation_id', 'type', 'relation_type', 'product_id', 'warehouse_id', 'created_at', 'created_by')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function with(): array
    {
        return [
            'products' => $this->products(),
            'headers' => $this->headers(),
            'warehouses' => \App\Models\Warehouse::all(),
            'users' => \App\Models\User::all(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Stok Hareketleri" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            @if (empty($expanded))
                <x-button label="Tüm Satırları Genişlet" wire:click="expandAll" responsive icon="o-chevron-right"/>
            @else
                <x-button label="Tüm Satırları Daralt" wire:click="collapseAll" responsive icon="o-chevron-down"/>
            @endif

            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"/>
        </x-slot:actions>
    </x-header>

    <!-- SUMMARY SECTION -->
    <div class="bg-white shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat title="Toplam Ürün" value="{{ $products->sum('total_quantity') }}" icon="o-cube"/>
            <x-stat title="Toplam Giriş"
                    value="{{ $products->where('type', \App\Enums\StockProcessType::IN)->sum('total_quantity') }}"
                    icon="o-arrow-down" class="text-green-600"/>
            <x-stat title="Toplam Çıkış"
                    value="{{ $products->where('type', \App\Enums\StockProcessType::OUT)->sum('total_quantity') }}"
                    icon="o-arrow-up" class="text-red-600"/>
        </div>
    </div>

    <!-- TABLE  -->
    <x-card>
        <x-table with-pagination :headers="$headers" :rows="$products" wire:model="expanded" :sort-by="$sortBy"
                 expandable expandable-key="warehouse_id">
            @php
                /** @var \App\Models\ProductTransaction $transaction */
            @endphp

            @scope('expansion', $transaction)
            <div>
                @if ($transaction->relation_type->isPurchase())
                    <x-table :headers="[
        ['key' => 'name', 'label' => 'Adı'],
        ['key' => 'qty', 'label' => 'Adet'],
    ]" :rows="$transaction->getVariantListAsTable($transaction->product_id)"/>
                @endif

                    @if ($transaction->relation_type->isSale())
                        <x-table :headers="[
        ['key' => 'name', 'label' => 'Adı'],
        ['key' => 'qty', 'label' => 'Adet'],
    ]" :rows="$transaction->getVariantSaleListAsTable($transaction->product_id)"/>
                    @endif
            </div>
            @endscope

            @scope('cell_product.name',$transaction)
            <x-button class="btn btn-sm" :label="$transaction->product->name"
                      class="btn-outline"
                      link="/product-variants?stock_code={{ $transaction->product->stock_code }}"/>
            @endscope

            @scope('cell_warehouse.name',$transaction)
            <x-button class="btn btn-sm" :label="$transaction->warehouse->name"
                      class="btn-outline"
                      link="/warehouses/{{  $transaction->warehouse_id }}/edit"/>
            @endscope

            @scope('cell_quantity',$transaction)
            {{ $transaction->getLineTotalQuantity() }}
            @endscope

            @scope('cell_supplier_or_contact_name',$transaction)
            @if ($transaction->relation_type->isPurchase())
                {{ $transaction->purchase->supplier->name }}
            @endif

            @if ($transaction->relation_type->isSale())
                {{ $transaction->sale?->contact?->name }}
            @endif

            @if ($transaction->relation_type->isSaleReturn())
                {{ $transaction->saleReturn->sale->contact?->name }}
            @endif

            @if ($transaction->relation_type->isPurchaseReturn())
                {{ $transaction->purchaseReturn->purchase->supplier?->name }}
            @endif

            @if ($transaction->relation_type->isSample())
                {{ $transaction->sample->getContactName() }}
            @endif
            @endscope

            @scope('cell_relation_type',$transaction)
            @if ($transaction->relation_type->isPurchase())
                <x-button @click="window.location='/purchases/{{ $transaction->relation_id }}/edit'"
                          :label="$transaction->textWithPurchaseNo()" class="btn-outline"/>
            @elseif ($transaction->relation_type->isSale())
                <x-badge :value="$transaction->relation_type->text()" class="badge-error"/>
            @elseif($transaction->relation_type->isSample())
                <x-button @click="window.location='/samples/{{ $transaction->relation_id }}/edit'"
                          :label="$transaction->textWithSampleNo()" class="btn-outline"/>
            @else
                {{ $transaction->relation_type->text() }}
            @endif
            @endscope

            @scope('cell_type',$transaction)
            @if ($transaction->type->isIn())
                <x-badge :value="$transaction->type->text()" class="badge-success"/>
            @endif

            @if ($transaction->type->isOut() || $transaction->type->isTransfer())
                <x-badge :value="$transaction->type->text()" class="badge-error"/>
            @endif
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
</div>
