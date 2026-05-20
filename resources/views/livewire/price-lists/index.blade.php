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

    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filtre temizlendi');
    }

    public function delete(\App\Models\PriceList $priceList): void
    {
        if (\App\Models\Proposal::where('price_list_id', $priceList->id)->exists()) {
            throw \Mary\Exceptions\ToastException::error('Bu fiyat listesi kullanımda, silinemez!');
        }

        $priceList->delete();

        $this->success("{$priceList->name} silindi");
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Fiyat Listesi', 'class' => 'w-84'],
            ['key' => 'contactGroup.name', 'label' => 'Cari Grubu', 'class' => 'w-64'],
            ['key' => 'type', 'label' => 'Türü', 'class' => 'w-64'],
            ['key' => 'value', 'label' => 'Değeri', 'class' => 'w-64'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
        ];
    }

    public function priceList(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\PriceList::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));;
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'priceList' => $this->priceList(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Fiyat Listesi" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtreler" @click="$wire.drawer = true" responsive icon="o-funnel"/>
            <x-button label="Yeni" link="/price-lists/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table link="price-lists/{id}/edit" with-pagination :headers="$headers" :rows="$priceList" :sort-by="$sortBy">
            @php
                /** @var \App\Models\PriceList $priceList */
            @endphp
            @scope('actions', $priceList )
            <x-button icon="o-trash" wire:click="delete('{{ $priceList['id'] }}')" wire:confirm="Emin misiniz?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            @endscope

            @scope('cell_type',$priceList)
            {{ $priceList->type->text() }}
            @endscope

        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filtreler" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </div>

        <x-slot:actions>
            <x-button label="Temizle" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Kapat" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
