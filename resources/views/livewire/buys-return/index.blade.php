<?php

use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {


    public function users(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\Sample::query()
            ->paginate(5); // No more `->get()`
    }


    public function headers(): array
    {
        return
            [

                ['key' => 'contact_name', 'label' => 'Müşteri'],
                ['key' => 'invoice_no', 'label' => 'Satış No'],
                ['key' => 'warehause.name', 'label' => 'Teslimat Deposu'],
                ['key' => 'state', 'label' => 'iade Faturası'],
            ];
    }

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

    <x-header title="Satınalma İade" separator>

        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" wire:click="$toggle('showDrawer2')"/>
            <x-button label="Yeni" link="/buys-return/create" responsive icon="o-plus" class="btn-primary"/>

        </x-slot:actions>

    </x-header>

    <x-card>
        <x-table link="buys-return/{id}/edit" :headers="$headers" :rows="$warehouses">


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
