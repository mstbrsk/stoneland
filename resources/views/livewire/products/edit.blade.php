<?php

use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;

    public \App\Models\Product $product;

    #[\Livewire\Attributes\Rule('required')]
    public string $name = '';

    public string $stock_code = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?float $sales_price = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?float $cost = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?float $tax_rate = null;

    #[\Livewire\Attributes\Rule('required')]
    public ?int $unit_id = null;

    #[\Livewire\Attributes\Rule('nullable|max:1024')]
    public $photo = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?bool $can_purchase = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?bool $can_sale = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?bool $allow_negative_stock = null;

    #[\Livewire\Attributes\Rule('required')]
    public int $warehouse_id;

    #[\Livewire\Attributes\Rule('required')]
    public string $created_by;

    #[\Livewire\Attributes\Rule('required')]
    public string $updated_by;

    public string $selectedTab = 'stock-info-tab';

    public bool $historyDrawer = false;

    public function mount(): void
    {
        empty($this->product->cost) && $this->product->cost = 0;

        $this->fill($this->product);
    }

    public function showHistoryDrawer()
    {
        $this->historyDrawer = true;
    }

    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $this->product->update($data);

        if ($this->photo && $this->product->isDirty('photo')) {
            $url = $this->photo->store('products', 'public');
            $this->product->update(['photo' => "/storage/$url"]);
        }

        log_action(message: 'Stok kartı güncellendi', relationType: 'Product', relationId: $this->product->id);

        $this->success('Stok kartı güncellendi.', redirectTo: '/products');
    }

    public function with(): array
    {
        return [
            'warehouses' => \App\Models\Warehouse::all(),
            'units' => \App\Models\Unit::all(),

            'productAttributes' => collect($this->product->product_attributes),

            'taxRates' => collect(config('sap.tax_rates'))->toArray(),
        ];
    }
};
?>

<div>
    <x-header title="Stok Kartı Güncelle" separator/>

    <livewire:action-log key="{{ Str::random() }}" :relation-id="$product->id" :show-history-drawer="$historyDrawer"/>

    <x-form wire:submit="save">

        <x-tabs wire:model="selectedTab">
            <x-tab name="stock-info-tab" label="Stok Kartı" icon="o-qr-code">
                <div class="lg:grid grid-cols-5">
                    <div class="col-span-1">
                        <x-header title="Stok Kartı" subtitle="Stok kartı bilgilerini giriniz" size="text-2xl"/>

                        <x-file change-text="Değiştir" label="Ürün Fotosu" wire:model="photo"
                                accept="image/png, image/jpeg"{{-- crop-after-change--}}>
                            <img src="{{ $product->photo ?? '/empty-user.jpg' }}" class="h-40 rounded-lg"/>
                        </x-file>
                    </div>

                    <div class="col-span-3 grid gap-3">
                        <x-choices-offline icon="o-building-office" single label="Depo Yeri" wire:model="warehouse_id"
                                           :options="$warehouses"
                                           required
                                           style="height: 45px"/>

                        <x-input icon="o-cube" label="Ürün Adı" wire:model="name" required/>
                        <x-input label="Stok Kodu" wire:model="stock_code" required disabled icon="o-qr-code"/>
                        <x-input icon="o-credit-card" label="Satış Fiyatı" wire:model="sales_price"/>

                        <x-choices-offline single :options="$taxRates" label="Vergi Oranı" wire:model="tax_rate" style="height: 45px"/>

                        <x-input icon="o-banknotes" label="Maliyet" wire:model="cost"/>
                        <x-select icon="o-arrows-pointing-in" label="Birim" wire:model="unit_id" :options="$units"
                                  placeholder="Seçiniz" required/>

                        <x-checkbox label="Açığa Satışa İzin Ver" wire:model="allow_negative_stock"/>
                        <x-checkbox label="Satılabilir" wire:model="can_sale"/>
                        <x-checkbox label="Satın Alınabilir" wire:model="can_purchase"/>
                    </div>
                </div>
            </x-tab>

            @if ($product->product_attributes)
                <x-tab name="stock-attributes-tab" label="Nitelikler" icon="o-squares-plus">
                    <livewire:products.product-attribute-repeater :is-edit="true"
                                                                  :selected-product-attributes="$productAttributes"/>
                </x-tab>
            @endif
        </x-tabs>

        <hr class="my-5"/>

        <x-slot:actions>
            <x-button label="İptal" link="/products"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
