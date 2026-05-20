<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public \App\Models\Currency $currency;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required')]
    public string $code = '';

    #[Rule('sometimes')]
    public string $symbol_left = '';

    #[Rule('sometimes')]
    public string $symbol_right = '';

    #[Rule('sometimes')]
    public ?float $value = null;

    #[Rule('required')]
    public string $updated_by;

    public string $updateAtText = '';
    public string $hintForCode = '';

    public function mount(): void
    {
        empty($this->currency->value) && $this->currency->value = 0;

        $this->fill($this->currency);

        $this->updateAtText =
            "Güncellenme tarihi: {$this->currency->updated_at->setTimezone('Europe/Istanbul')->format('d-m-Y H-i')}";

        $this->hintForCode = "Para birimi kodlarını <a target='_blank' href='https://www.xe.com/currency/'>buraya</a> tıklayarak bulabilirsiniz";
    }

    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $attribute = $this->currency->update($data);

        \Illuminate\Support\Facades\Artisan::call(\App\Console\Commands\UpdateExchangeRates::class);

        $this->success('Para birimi güncellendi.', redirectTo: '/currencies');
    }

    public function with(): array
    {
        return [
            //
        ];
    }


}; ?>

<div>
    <x-header title="Para Birimi Ekle" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Para Birimi" subtitle="Para birimi bilgilerini giriniz" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">
                <livewire:components.alert title="{!! $this->hintForCode !!}" icon="o-exclamation-triangle"/>

                <x-input label="Adı" wire:model="name" required/>
                <x-input label="Kodu" wire:model="code" required/>

                <x-input label="Sembol Sol" wire:model="symbol_left"/>
                <x-input label="Sembol Sağ" wire:model="symbol_right"/>
                <x-input label="Değeri" wire:model="value" readonly :hint="$this->updateAtText"/>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/currencies"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
