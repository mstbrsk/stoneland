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
    public string $contact_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public string $delivery_address_id = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public string $invoice_address_id = '';

    #[\Livewire\Attributes\Rule('required')]
    public bool $has_contact = false;

    #[\Livewire\Attributes\Rule('required')]
    public string $proposal_no;


    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $selectedProducts = null;

    public array $deliveryAddresses = [];
    public array $invoiceAddresses = [];

    public function mount()
    {
        $this->fill([

            'proposal_no' => generate_proposal_no(),

        ]);
    }


    public function updatedContactId(mixed $value)
    {
        $this->deliveryAddresses = \App\Models\Address::where([
            'contact_id' => $value,
            'type' => \App\Enums\Address\AddressType::DELIVERY
        ])
            ->get()
            ->toArray();

        $this->invoiceAddresses = \App\Models\Address::where([
            'contact_id' => $value,
            'type' => \App\Enums\Address\AddressType::INVOICE
        ])
            ->get()
            ->toArray();
    }

    public function with(): array
    {
        return [
            'paymentConditions' => \App\Models\PaymentCondition::get(),

            'currencies' => \App\Models\Currency::get(),

            'contacts' => \App\Models\Contact::get(),

            'pricelist' => \App\Models\PriceList::get(),

            'proposal' => \App\Models\Proposal::get(),

            'productList' => \App\Models\Product::all()->map(fn(\App\Models\Product $product) => [
                'id' => $product->id,
                'text' => $product->name,
            ])->toArray(),

            'cargoProviders' => config('sap.cargo_providers'),

        ];
    }

}; ?>

<div>
    <x-header title="Sevkiyat" separator/>
    <x-form wire:submit="save">

        <div class="lg:grid grid-cols-2 gap-6">
            <x-input wire:model="proposal_no" label="Sevkiyat Numarası" readonly/>

            <x-choices-offline searchable single label="Müşteri" wire:model.live="contact_id" :options="$contacts"
                               required style="height: 45px"/>


            <x-choices-offline searchable single label="Teslim Adresi" wire:model="delivery_address_id"
                               :options="$deliveryAddresses"
                               required style="height: 45px"/>

            <x-datetime label="Sipariş Tarihi" wire:model="order_date"/>


            <x-choices-offline searchable single label="Fatura Adresi" wire:model="invoice_address_id"
                               :options="$invoiceAddresses"
                               required style="height: 45px"/>


            <x-choices-offline icon="o-rocket-launch" style="height: 45px" searchable label="Kargo"
                               wire:model="cargo_provider" :options="$cargoProviders" single/>
        </div>

        <div class="lg:grid grid-cols-2 gap-6">


        </div>




        <x-slot:actions>
            <x-button label="İptal"/>
            <x-button label="Kaydet" class="btn-primary" type="submit" spinner="save"/>
        </x-slot:actions>


    </x-form>
</div>
