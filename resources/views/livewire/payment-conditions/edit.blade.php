<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public \App\Models\PaymentCondition $paymentConditions;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required')]
    public string $updated_by;

    public function mount(): void
    {
        $this->fill($this->paymentConditions);
    }

    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $data = $this->validate();
        $attribute = $this->paymentConditions->update($data);

        $this->success('Ürün birimi  güncdellendi.', redirectTo: '/payment-conditions');

    }

}; ?>


<div>
    <x-header title="Ödeme Koşullarını Güncelle" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Ödeme Koşulları" subtitle="Ödeme koşullarını giriniz" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">

                <x-input label="Adı" wire:model="name" required/>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/payment-conditions"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
