<?php

use App\Models\Address;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

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
        $this->success('Filtre temizlendi.', position: 'toast-bottom');
    }

    public function delete(Address $address): void
    {
        if (!check_permission('delete_address')) {
            throw \Mary\Exceptions\ToastException::error('Silme işlemi için yetkiniz yok!');
        }

        if (Warehouse::where('address_id', $address->id)->exists()) {
            throw \Mary\Exceptions\ToastException::error('Bu depo kullanımda, silinemez!');
        }

        $address->delete();

        $this->success("{$address->name} adlı adres silindi");
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Adres Adı', 'class' => 'w-74'],
            ['key' => '_contact_name_', 'label' => 'Firma', 'class' => 'w-84'],
            ['key' => 'type', 'label' => 'Türü', 'class' => 'w-64'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
        ];
    }

    public function addresses(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Address::query()
            ->when($this->search, fn(Builder $q) => $q->whereLike(['name', 'updatedBy.name', 'type', 'contact.name'], $this->search))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function with(): array
    {
        return [
            'addresses' => $this->addresses(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Adresler" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

       <x-slot:actions>

            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"/>
            <x-button label="Yeni" link="/addresses/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table link="addresses/{id}/edit" with-pagination :headers="$headers" :rows="$addresses" :sort-by="$sortBy">
            @scope('cell_type', $address)
            {{ $address->type->text() }}
            @endscope

            @scope('cell__contact_name_', $address)
            {{ $address->contact->name ?? 'Berka' }}
            @endscope

            @scope('actions', $address)
            <x-button icon="o-trash" wire:click="delete('{{ $address['id'] }}')" wire:confirm="Emin misiniz?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filtre" right separator with-close-button class="lg:w-1/3">
       <div class="grid gap-5">
            <x-input placeholder="Ara..." .../>
        </div>

        <x-slot:actions>
            <x-button label="Temizle" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Tamam" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
