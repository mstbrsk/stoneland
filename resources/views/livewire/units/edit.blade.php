<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public \App\Models\Unit $unit;

    #[Rule('required')]
    public string $name = '';


    #[Rule('required')]
    public string $updated_by;

    public function mount(): void
    {
        $this->fill($this->unit);
    }

    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $attribute = $this->unit->update($data);

        $this->success('Ürün birimi  güncdellendi.', redirectTo: '/units');


    }

}; ?>

<div>
    <x-header title="Ölçü Birimi Güncelle" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Ölçü Birimi" subtitle="Ölçü birimi bilgilerini giriniz" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">

                <x-input label="Adı" wire:model="name" required/>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/units"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
