<?php

use App\Models\Address;
use App\Models\Warehouse;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;

    public function save(): void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $unit = \App\Models\Unit::create($data);

        log_action(message: 'Ölçü birimi oluşturuldu', relationType: 'Unit', relationId: $unit->id);

        $this->success('Ölçü birimi oluşturuldu.', redirectTo: '/units');

        $this->dispatch('unit-created');
    }

    public function with(): array
    {
        return [
            //
        ];
    }
};
?>

<div>
    <x-header title="Ölçü Birimi Ekle" separator/>
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
