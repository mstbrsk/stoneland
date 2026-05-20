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

    #[Rule('required')]
    public string $code = '';

    #[Rule('sometimes')]
    public string $symbol_left = '';

    #[Rule('sometimes')]
    public string $symbol_right = '';

    #[Rule('sometimes')]
    public ?float $value = null;

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;

    public string $hintForCode = '';

    public function mount(): void
    {
        $this->hintForCode = "Para birimi kodlarını <a target='_blank' href='https://www.xe.com/currency/'>buraya</a> tıklayarak bulabilirsiniz";
    }

    public function save(): void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $currency = \App\Models\Currency::create($data);

        \Illuminate\Support\Facades\Artisan::call(\App\Console\Commands\UpdateExchangeRates::class);

        log_action(message: 'Para birimi oluşturuldu', relationType: 'Currency', relationId: $currency->id);

        $this->success('Para birimi oluşturuldu.', redirectTo: '/currencies');
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
    <x-header title="Para Birimi Ekle" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Para Birimi" subtitle="Para birimi bilgilerini giriniz" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">
                <livewire:components.alert title="{!! $this->hintForCode !!}" icon="o-exclamation-triangle" />

                <x-input label="Adı" wire:model="name" required />
                <x-input label="Kodu" wire:model="code" required/>

                <x-input label="Sembol Sol" wire:model="symbol_left"/>
                <x-input label="Sembol Sağ" wire:model="symbol_right"/>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/warehouses"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
