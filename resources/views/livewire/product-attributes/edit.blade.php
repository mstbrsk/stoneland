<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public \App\Models\ProductAttribute $productAttribute;

    #[Rule('required|min:3|max:255')]
    public string $name;

    #[Rule('required')]
    public string $updated_by;

    public function mount(): void
    {
        $this->fill($this->productAttribute);
    }

    public function getValues(): \Illuminate\Support\Collection
    {
        return collect($this->productAttribute->values);
    }

    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $this->productAttribute->update($data);

        $this->success('Ürün niteliği başarıyla güncellendi.', redirectTo: '/product-attributes');

        $this->dispatch('product-attribute-updated');
    }

    public function with(): array
    {
        return [
            'allValues' => $this->getValues(),
        ];
    }
};
?>

<div class="bg-white shadow-md rounded-lg p-6 max-w-7xl mx-auto">
    <x-header title="Ürün Niteliği Düzenle" subtitle="Ürün niteliği bilgilerini güncelleyin" separator class="mb-6"/>

    <x-form wire:submit="save" class="space-y-6">
        <div class="lg:grid lg:grid-cols-5 lg:gap-8">
            <div class="col-span-2">
                <x-header title="Ürün Niteliği" subtitle="Nitelik bilgilerini giriniz" size="text-xl" class="mb-4"/>
                <div class="bg-gray-100 p-4 rounded-md">
                    <p class="text-sm text-gray-600">Ürün nitelikleri, bir ürünün özelliklerini ve karakteristiklerini tanımlayan önemli bilgilerdir. Bu nitelikler, müşterilerin ürünler hakkında daha fazla bilgi edinmelerine ve satın alma kararlarını daha bilinçli bir şekilde vermelerine yardımcı olur. Aynı zamanda, ürünlerin sınıflandırılmasını, filtrelenmesini ve karşılaştırılmasını kolaylaştırır.
                    </p>
                </div>
            </div>
            <div class="col-span-3 space-y-4">
                <x-input label="Nitelik Adı" wire:model="name" placeholder="Örn: Renk, Boyut, Malzeme" required class="w-full"/>

                <div class="bg-gray-50 p-4 rounded-md">
                    <h3 class="text-lg font-semibold mb-2">Nitelik Değerleri</h3>
                    <livewire:product-attributes.product-attribute-repeater
                        :selected-product-attributes="$allValues" :is-edit="true"/>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <div class="flex justify-end space-x-3">
                <x-button label="İptal" link="/product-attributes" class="btn-secondary"/>
                <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
            </div>
        </x-slot:actions>
    </x-form>
</div>
