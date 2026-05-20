<?php

use App\Models\Sample;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;


new class extends Component {

    use Toast;
    use \Livewire\WithFileUploads;
    use Mary\Traits\WithMediaSync;

    #[\Livewire\Attributes\Rule('required')]
    public bool $has_contact = false;


    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $selectedProducts = null;


    #[On('raise-selected-products')]
    public function setSelectedProducts(?array $products = null)
    {
        $this->selectedProducts = $products;
    }


    public function with(): array
    {
        return [
            'paymentConditions' => \App\Models\PaymentCondition::get(),

            'currencies' => \App\Models\Currency::get(),

            'contacts' => \App\Models\Contact::get(),

            'pricelist' => \App\Models\PriceList::get(),

            'productList' => \App\Models\Product::all()->map(fn(\App\Models\Product $product) => [
                'id' => $product->id,
                'text' => $product->name,
            ])->toArray()
        ];
    }

}; ?>

<div>
    <x-header title="Satınalma İade" separator/>
    <x-form wire:submit="save">
        <x-toggle wire:model="has_contact" label="İade Faturası"/>
        <div class="lg:grid grid-cols-2 gap-6">
            <x-input label="Müşteri" wire:model="name"/>
            <x-input label="Satış Numarası" wire:model="name" readonly/>
        </div>

        <div class="lg:grid grid-cols-2 gap-6">
            <x-input wire:model="sales_no" label="Teslimat Deposu"/>


            <x-choices-offline x-show="$wire.has_contact===true " searchable single label="İade Faturası"
                               wire:model="contact_id"
                               style="height: 45px;"/>


        </div>


        <livewire:buys-return.repeater/>


        <x-slot:actions>
            <x-button label="İptal"/>
            <x-button label="Kaydet" class="btn-primary" type="submit" spinner="save"/>
        </x-slot:actions>


    </x-form>
</div>
