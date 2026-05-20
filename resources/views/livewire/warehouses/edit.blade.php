<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;


new class extends Component {
    use Toast;

    public \App\Models\Warehouse $warehouse;

    #[Rule('required')]
    public string $name = '';

    #[Rule('sometimes')]
    public string $short_name = '';

    #[Rule('sometimes')]
    public ?string $color = '';

    #[Rule('sometimes')]
    public ?string $address_id = null;


    #[Rule('required')]
    public string $updated_by;

    public function mount(): void
    {
        $this->fill($this->warehouse);
    }

    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $this->warehouse->update($data);

        $this->success('Depo başarıyla güncellendi.', redirectTo: '/warehouses');
    }

    public function with(): array
    {
        return [
            'addresses' => \App\Models\Address::where('is_my_address', true)->get(),
        ];
    }

}; ?>

<div>
    <div>
        <x-header title="Depo Güncelle" separator/>
        <x-form wire:submit="save">
            <div class="lg:grid grid-cols-5">
                <div class="col-span-1">
                    <x-header title="Depo" subtitle="Depo bilgilerini güncelleyiniz" size="text-2xl"/>
                </div>
                <div class="col-span-3 grid gap-3">

                    <x-input label="Depo Adı" wire:model="name" required/>
                    <x-input label="Kısa Ad" wire:model="short_name"/>
                    <x-choices label="Adres" wire:model="address_id" :options="$addresses" single style="height: 45px"/>

                    <x-colorpicker label="Renk" wire:model="color"/>
                </div>
            </div>

            <x-slot:actions>
                <x-button label="İptal" link="/warehouses"/>
                <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
            </x-slot:actions>
        </x-form>
    </div>
</div>
