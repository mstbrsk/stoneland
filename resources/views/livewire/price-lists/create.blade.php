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
    public string $contact_group_id;

    #[Rule('required')]
    public int $type;

    #[Rule('required')]
    public float $value;

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;

    public string $prefix = '%';

    public function mount()
    {
        $this->type = \App\Enums\PriceListType::PERCENTAGE_BASED->value;
    }

    public function updatedType($value)
    {
        $this->prefix = \App\Enums\PriceListType::from($value)->sign();
    }

    public function save(): void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $priceList = \App\Models\PriceList::create($data);

        log_action(message: 'Fiyat Listesi oluşturuldu', relationType: 'PriceList', relationId: $priceList->id);

        $this->success('Fiyat Listesi oluşturuldu.', redirectTo: '/price-lists');

        $this->dispatch('unit-created');
    }

    public function with(): array
    {
        return [
            'groups' => \App\Models\ContactGroup::all(),
            'types' => \App\Enums\PriceListType::listForMaryUI(),
        ];
    }
};
?>

<div>
    <x-header title="Fiyat Listesi Ekle" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Fiyat Listesi" subtitle="Fiyat Listesi bilgilerini giriniz" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">

                <x-input label="Adı" wire:model="name" required/>

                <x-choices-offline style="height: 45px" single searchable
                                   required
                                   :options="$groups"
                                   label="Grubu"
                                   wire:model="contact_group_id"/>

                <x-radio label="Türü" :options="$types" wire:model.lazy="type" required/>

                <x-input wire:model="value" label="Değer" type="number" :prefix="$prefix" required/>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/price-lists"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
