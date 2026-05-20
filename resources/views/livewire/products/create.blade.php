<?php

use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;

    #[\Livewire\Attributes\Rule('required')]
    public string $name = '';

    #[\Livewire\Attributes\Rule('required|unique:products', message: [
        'unique' => 'Bu stok kodu kullanılıyor'
    ])]
    public string $stock_code = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?float $sales_price = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?float $cost = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?float $tax_rate = null;

    #[\Livewire\Attributes\Rule('required')]
    public ?int $unit_id = null;

    #[\Livewire\Attributes\Rule('nullable|image|max:1024')]
    public ?\Illuminate\Http\UploadedFile $photo = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $product_attributes = null;

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

    #[\Livewire\Attributes\On('raise-updated-attribute-items')]
    public function setAttributeItems(array $attributeItems)
    {
        $this->product_attributes = $attributeItems;
    }

    public function save(): void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $product = \App\Models\Product::create($data);

        $this->createVariants($product);

        if ($this->photo) {
            $url = $this->photo->store('products', 'public');
            $product->update(['photo' => "/storage/$url"]);
        }

        log_action(message: 'Stok kartı oluşturuldu', relationType: 'Product', relationId: $product->id);

        $this->success('Stok kartı oluşturuldu.', redirectTo: '/products');
    }

    public function createVariants(\App\Models\Product $product): void
    {
        // No variant
        if (empty($product->product_attributes)) {
            \App\Models\ProductVariant::create([
                'product_name' => $product->name,
                'stock_code' => $product->stock_code,
                'product_id' => $product->id,
                'attribute_items' => null,
                'created_by' => $product->created_by,
                'updated_by' => $product->updated_by,
            ]);

            return;
        }

        // Has variant
        $values = collect($product->product_attributes)->map(function (array $attributeList) {
            return $attributeList['values'];
        })
            ->values()
            ->toArray();

        $variants = collect(array_shift($values))->crossJoin(...$values)->all();

        foreach ($variants as $variant) {
            \App\Models\ProductVariant::create([
                'product_name' => $product->name,
                'stock_code' => $product->stock_code,
                'product_id' => $product->id,
                'attribute_items' => json_encode($variant),
                'created_by' => $product->created_by,
                'updated_by' => $product->updated_by,
            ]);
        }

        log_action(message: 'Ürün varyantları oluşturuldu', relationType: 'ProductVariant', relationId: $product->id);
    }

    public function with(): array
    {
        return [
            'warehouses' => \App\Models\Warehouse::all(),
            'units' => \App\Models\Unit::all(),
            'taxRates' => collect(config('sap.tax_rates'))->toArray(),
        ];
    }
};
?>

<div>
    <x-header title="Stok Kartı Ekle" separator/>
    <x-form wire:submit="save">

        <x-tabs wire:model="selectedTab">
            <x-tab name="stock-info-tab" label="Stok Kartı" icon="o-qr-code">
                <div class="lg:grid grid-cols-5">
                    <div class="col-span-1">
                        <x-header title="Stok Kartı" subtitle="Stok kartı açacağınız zaman eğer ürünün hem beden hem de renk değerleri varsa önce beden  sonra renk varyasyonlarını girin!!! " size="text-2xl"/>

                        <x-file change-text="Değiştir" label="Ürün Fotosu" wire:model="photo"
                                accept="image/png, image/jpeg"{{-- crop-after-change--}}>
                            <img src="{{ '/empty-user.jpg' }}" class="h-40 rounded-lg"/>
                        </x-file>
                    </div>

                    <div class="col-span-3 grid gap-3">

                        <x-choices-offline single label="Depo Yeri" wire:model="warehouse_id" :options="$warehouses"
                                           required
                                           style="height: 45px"/>

                        <x-input label="Ürün Adı" wire:model="name" required/>
                        <x-input label="Stok Kodu" wire:model="stock_code" required/>
                        <x-input label="Satış Fiyatı" wire:model="sales_price"/>

                        <x-choices-offline single :options="$taxRates" label="Vergi Oranı" wire:model="tax_rate"
                                           style="height: 45px"/>

                        <x-input label="Maliyet" wire:model="cost"/>
                        <x-select label="Birim" wire:model="unit_id" :options="$units" placeholder="Seçiniz" required/>

                        <x-toggle label="Açığa Satışa İzin Ver" wire:model="allow_negative_stock"/>
                        <x-toggle label="Satılabilir" wire:model="can_sale"/>
                        <x-toggle label="Satın Alınabilir" wire:model="can_purchase"/>
                    </div>
                </div>
            </x-tab>

            <x-tab name="stock-attributes-tab" label="Nitelikler" icon="o-squares-plus">
                <livewire:products.product-attribute-repeater/>
            </x-tab>
        </x-tabs>

        <hr class="my-5"/>

        <x-slot:actions>
            <x-button label="İptal" link="/products"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
