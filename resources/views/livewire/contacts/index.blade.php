<?php

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filtre temizlendi.', position: 'toast-bottom');
    }

    public function delete(Contact $contact): void
    {
        //Todo: Make validation for sales and etc!
        if (
            \App\Models\Purchase::where('supplier_id', $contact->id)->exists()
        ) {
            throw \Mary\Exceptions\ToastException::error('Bu cari kullanımda, silinemez!');
        }

        $contact->delete();

        $this->success("{$contact->name} adlı cari silindi");
    }

    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => 'Kod', 'class' => 'w-30'],
            ['key' => 'name', 'label' => 'Adı', 'class' => 'w-30'],
            ['key' => 'phone', 'label' => 'Telefon', 'class' => 'w-30'],
            ['key' => 'mobile', 'label' => 'Türü', 'Mobil' => 'w-30'],
            ['key' => 'email', 'label' => 'E-Posta'],
            ['key' => 'updatedBy.name', 'label' => 'Kullanıcı', 'sortable' => false],
            ['key' => 'ticket_list', 'label' => 'Etiketler'],
            ['key' => 'city.name', 'label' => 'Şehir'],
            /*            ['key' => 'district', 'label' => 'Semt/İlçe'],*/
        ];
    }

    public function contacts(): LengthAwarePaginator
    {
        return Contact::query()
            ->when($this->search, fn(Builder $q) => $q->whereLike(['name', 'phone', 'mobile', 'email', 'updatedBy.name', 'createdBy.name', 'tickets', 'city.name'], $this->search))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function with(): array
    {
        return [
            'contacts' => $this->contacts(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Kontaklar" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"/>
            <x-button label="Yeni" link="/contacts/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table link="contacts/{id}/edit" with-pagination :headers="$headers" :rows="$contacts" :sort-by="$sortBy">
            @php
                /** @var Contact $contact */
            @endphp
            @scope('actions', $contact)
            <x-button icon="o-trash" wire:click="delete('{{ $contact['id'] }}')" wire:confirm="Emin misiniz?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            @endscope

            @scope('cell_ticket_list',$contact)
            <span>{{ $contact->ticketList() }}</span>
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
