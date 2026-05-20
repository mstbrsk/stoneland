<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    #[Rule('required')]
    public string $name;

    #[Rule('required')]
    public array $values;

    #[\Livewire\Attributes\On('raise-updated-selected-product-attributes')]
    public function setValues(?array $attributes)
    {
        $this->values = $attributes;
    }

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;

    public function save(): void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $attribute = \App\Models\ProductAttribute::create($data);

        foreach ($this->values as $item) {
            \App\Models\ProductAttributeItem::create([
                'product_attribute_id' => $attribute->id,
                'value' => $item['attribute']
            ]);
        }

        $this->success('Ürün niteliği oluşturuldu.', redirectTo: '/product-attributes');

        $this->dispatch('product-attribute-created');
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
    <x-header title="Ürün Niteliği Ekle" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Ürün Niteliği" subtitle="Nitelik bilgilerini giriniz" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">
                <x-input label="Adı" wire:model="name" required/>

                <livewire:product-attributes.product-attribute-repeater/>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/product-attributes"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
