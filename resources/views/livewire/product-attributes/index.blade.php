<?php

use App\Models\Warehouse;
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
        $this->success('Filtre temizlendi');
    }

    public function delete(\App\Models\ProductAttribute $attribute): void
    {
       /* if (\App\Models\Product::where('unit_id', $attribute->id)->exists()) {
            throw \Mary\Exceptions\ToastException::error('Bu birim kullanımda, silinemez!');
        }*/

        $attribute->items()->each(fn(\App\Models\ProductAttributeItem $item) => $item->delete());

        $attribute->delete();

        $this->success("{$attribute->name} silindi");
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Adı', 'class' => 'w-64'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
        ];
    }

    public function productAttributes(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\ProductAttribute::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));;
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'productAttributes' => $this->productAttributes(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Stok Nitelikleri" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtreler" @click="$wire.drawer = true" responsive icon="o-funnel"/>
            <x-button label="Yeni" link="/product-attributes/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table link="product-attributes/{id}/edit" with-pagination :headers="$headers" :rows="$productAttributes" :sort-by="$sortBy">
            @scope('actions', $unit)
            <x-button icon="o-trash" wire:click="delete({{ $unit['id'] }})" wire:confirm="Emin misiniz?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filtreler" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Ara..." .../>
        </div>

        <x-slot:actions>
            <x-button label="Temizle" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Kapat" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
