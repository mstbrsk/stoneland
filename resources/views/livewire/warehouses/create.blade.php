<?php

use App\Models\Address;
use App\Models\Warehouse;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use WithFileUploads;

    #[Rule('required')]
    public string $name = '';

    #[Rule('sometimes')]
    public string $short_name = '';

    #[Rule('sometimes')]
    public string $color = '';

    #[Rule('sometimes')]
    public ?int $address_id = null;

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;

    public function save(): void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $warehouse = Warehouse::create($data);

        log_action(message: 'Depo oluşturuldu', relationType: 'Warehouse', relationId: $warehouse->id);

        $this->success('Depo başarıyla oluşturuldu.', redirectTo: '/warehouses');

        $this->dispatch('warehouse-created');
    }

    public function with(): array
    {
        return [
            'addresses' => Address::where('is_my_address', true)->get(),
        ];
    }
};
?>

<div>
    <x-header title="Depo Ekle" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Depo" subtitle="Depo bilgilerini giriniz" size="text-2xl"/>
            </div>

            <div class="col-span-3 grid gap-3">

                <x-input label="Depo Adı" wire:model="name" required/>
                <x-input label="Kısa Ad" wire:model="short_name"/>
                <x-choices label="Adres" wire:model="address_id" :options="$addresses" single style="height: 65px"/>

                <x-colorpicker label="Renk" wire:model="color"/>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/warehouses"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
