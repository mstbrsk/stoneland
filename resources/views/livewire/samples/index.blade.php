<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    use Toast;

    public string $search = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function samples(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\Sample::query()
            ->when($this->search, fn(Builder $q) => $q->whereLike(['contact_name', 'contact.name'], "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(5); // No more `->get()`
    }

   /* public function delete(\App\Models\Sample $samples): void
    {

        $samples->delete();
        $this->success('Başarı ile silindi');

    }*/

    public function clear(): void
    {
        $this->reset();

        $this->success('Filtre temizlendi');
    }


    public function headers(): array
    {
        return
            [
                ['key' => 'contact_name', 'label' => 'Müşteri','class' => 'w-60'],
                ['key' => 'warehouse.name', 'label' => 'Teslimat Deposu'],
                ['key' => 'invoice_no', 'label' => 'İrsaliye No'],
                ['key' => 'returned_count', 'label' => 'Gelen'],
                ['key' => 'shipped_count', 'label' => 'Giden'],
                ['key' => 'status', 'label' => 'Durumu'],
            ];
    }

    public bool $showDrawer2 = false;
    public bool $myModal1 = false;

    public function with(): array
    {
        return [
            'samples' => $this->samples(),
            'headers' => $this->headers(),

        ];
    }
}; ?>

<div>
    <x-header title="Numuneler" separator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" wire:click="$toggle('showDrawer2')"/>
            <x-button label="Yeni" link="/samples/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

{{--
    @livewire('chatbox')
--}}

    <x-card>
        <x-table link="samples/{id}/edit" :headers="$headers" :rows="$samples">

            @php
                /** @var \App\Models\Sample $sample */
            @endphp
            @scope('cell_contact_name', $sample)
            {{ $sample->getContactName() }}
            @endscope

            @scope('cell_returned_count', $sample)
            {{ collect($sample->return_data)->where('returned', true)->count() }}
            @endscope

            @scope('cell_shipped_count', $sample)
            {{  collect($sample->data)->sum('qty') }}
            @endscope

            @scope('cell_status', $sample)
            {!!  $sample->status->textWithBadge() !!}
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
            <x-button label="Temizle" wire:click="clear"/>
            <x-button label="Confirm" class="btn-primary" icon="o-check"/>
        </x-slot:actions>
    </x-drawer>
</div>
