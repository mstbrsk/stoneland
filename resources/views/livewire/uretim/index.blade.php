<?php
use App\Models\Warehouse;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {

    use Toast;
    public string $search = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function users(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Warehouse::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))

            ->paginate(5); // No more `->get()`
    }


    public function  delete (Warehouse $warehouse) :void
    {
        if (\App\Models\Product::where('warehouse_id', $warehouse->id)->exists()) {
            throw \Mary\Exceptions\ToastException::error('Bu depo kullanımda, silinemez!');
        }

        $warehouse->delete();
        $this->success('Başarı ile silindi');

    }



    public function clear(): void
    {
        $this->reset();

        $this->success('Filtre temizlendi');
    }

   public function  headers(): array
    {
        return
        [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'name', 'label' => 'Nice Name'],
            ['key' => 'short_name', 'label' => 'fdf'],
        ];
     }




    public bool $myModal1 = false;

    public bool $showDrawer1 = false;
    public bool $showDrawer2 = false;

    public function with(): array
    {
        return [
            'warehouses' => $this->users(),
            'headers' => $this->headers(),

        ];
    }

}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Personal address" subtitle="Your home address" separator >

        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" wire:click="$toggle('showDrawer2')" />
            <x-button label="Yeni" link="/uretim/create" responsive icon="o-plus" class="btn-primary"/>

        </x-slot:actions>

    </x-header>

    <x-card>
        <x-table link="uretim/{id}/edit"  :headers="$headers" :rows="$warehouses"  >

        @scope('actions', $warehouse)
        <x-button icon="o-trash" wire:click="delete({{ $warehouse['id'] }})" wire:confirm="Emin misiniz?" spinner
                  class="btn-ghost btn-sm text-red-500"/>
        @endscope
        </x-table>
    </x-card>
    <x-drawer
        wire:model="showDrawer2"
        title="Hello"
        subtitle="Livewire"
        separator
        with-close-button
        class="w-11/12 lg:w-1/3"
        right
    >
        <div>Hey!</div>

        <x-slot:actions>
            <x-button label="Temizle" wire:click="clear" />
            <x-button label="Confirm" class="btn-primary" icon="o-check" />
        </x-slot:actions>
    </x-drawer>


</div>
