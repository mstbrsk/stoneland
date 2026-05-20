<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public \App\Models\Warehouse $warehouse;

    #[\Livewire\Attributes\Rule('required')]
    public string $return_order = '';
    #[\Livewire\Attributes\Rule('required')]
    public string $return_invoice = '';
    #[\Livewire\Attributes\Rule('sometimes')]
    public string $product_invoice = '';
    #[\Livewire\Attributes\Rule('required')]
    public string $state = '';

    #[Rule('required')]
    public string $updated_by;

    public function save():void
    {

        $this->updated_by = auth('web')->id();

        $data = $this->validate();


        $attribute = $this->warehouse->update($data);

        $this->success('İadeler  güncellendi.', redirectTo: '/sample');



    }


}; ?>

<div>
    <x-header title="İade" separator />
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Özellikleri"  size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">

                <x-input label="İade Siparişi" wire:model="return_order" required/>
                <x-input label="İade Faturası" wire:model="return_invoice" required/>
                <x-input label="Ürün Faturası" wire:model="product_invoice" />
                <x-input label="Durum" wire:model="state" />


            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/sample"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
