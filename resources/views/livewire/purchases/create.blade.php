<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;
    use Mary\Traits\WithMediaSync;

    #[\Livewire\Attributes\Rule('required')]
    public string $supplier_id;

    #[\Livewire\Attributes\Rule('required')]
    public ?string $currency_id;

    #[\Livewire\Attributes\Rule('required')]
    public string $purchased_at;

    #[\Livewire\Attributes\Rule('required')]
    public string $deadline_at;

    #[\Livewire\Attributes\Rule('sometimes')]
    public string|null $purchase_no;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $source_doc = null;

    #[\Livewire\Attributes\Rule('required')]
    public int $warehouse_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $invoice_no = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $dispatch_no = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $notes = null;

    #[\Livewire\Attributes\Rule('required')]
    public int $status = 1;

    #[\Livewire\Attributes\Rule('required')]
    public string $created_by;

    #[\Livewire\Attributes\Rule('required')]
    public string $updated_by;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $selectedProducts = null;

    #[\Livewire\Attributes\Rule(['images.*' => 'image|max:1024'])]
    public array $images = [];

    #[\Livewire\Attributes\Rule('sometimes')]
    public \Illuminate\Support\Collection $library;

    #[On('raise-selected-products')]
    public function setSelectedProducts(?array $products = null)
    {
        $this->selectedProducts = $products;
    }

    public function mount()
    {
        $this->fill([
            'library' => collect(),
            'purchase_no' => generate_purchase_no(),
        ]);
    }

    public function save(): void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $validated = $this->validate();

        $data = $this->except(['selectedProducts', 'images', 'allSelectedVariants']);

        $itemList = [];
        $purchaseId = Str::uuid();
        $data['id'] = $purchaseId;

        $items = $this->selectedProducts;

        if (empty($items)) {
            throw \Mary\Exceptions\ToastException::error('Lütfen ürün girişi yapınız!');
        }

        foreach ($items as $item) {
            if (empty($item['line_total'])) {
                throw \Mary\Exceptions\ToastException::error('Eksik alanları tamamlayın!');
            }
        }

        $currency = \App\Models\Currency::findOrFail($this->currency_id);
        $allVariants = collect($items)->map(fn(array $item) => $item['variants'])->toArray();

        $data['selected_items'] = $items;
        $data['selected_variants'] = $allVariants;

        foreach ($items as $key => $item) {
            if (empty($item['product_id'])) {
                $this->error('Ürün seçiniz!');
                return;
            }

            $itemList[] = [
                'id' => Str::uuid(),
                'purchase_id' => $purchaseId,

                'product_id' => $item['product_id'],
                'notes' => $item['notes'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'],
                'vat_line_total' => $item['vat_line_total'],
                'line_total' => $item['line_total'],
                'selected_variants' => json_encode($allVariants[$key]),
            ];
        }

        /** @var \App\Models\PurchaseVariant[] $allVariants */
        foreach ($itemList as $key => $item) {
            foreach ($allVariants[$key] as $variantId => $qty) {
                $variantList[] = [
                    'id' => Str::uuid(),
                    'purchase_id' => $purchaseId,
                    'purchase_item_id' => $item['id'],
                    'product_id' => $item['product_id'],
                    'variant_id' => $variantId,
                    'qty' => $qty
                ];
            }
        }

        $data['sub_total'] = collect($items)->sum(fn(array $item) => $item['unit_price'] * $item['qty']);

        $data['total'] = collect($items)->sum(function (array $item) {
            $linePrice = $item['unit_price'] * $item['qty'];
            $vatAmount = $linePrice * ($item['vat_rate'] / 100);
            return $linePrice + $vatAmount;
        });

        $data['quantity'] = collect($items)->sum('qty');

        $purchase = \App\Models\Purchase::create(to_case(collect($data)->except(['products', 'images'])->toArray()));

        \App\Models\PurchaseItem::insert($itemList);
        \App\Models\PurchaseVariant::insert($variantList);

        $this->syncMedia(model: $purchase, files: 'images', storage_subpath: 'purchases');

        $this->reset();

        $this->success('Satın alma siparişi oluşturuldu . ', redirectTo: '/purchases');

        log_action(message: 'Satın alma siparişi oluşturuldu', relationType: 'Purchase', relationId: $purchase->id);
    }

    public function with(): array
    {
        return [
            'suppliers' => \App\Models\Contact::suppliers()->get(),
            'currencies' => \App\Models\Currency::get(),
            'warehouses' => \App\Models\Warehouse::get(),

            'productList' => \App\Models\ProductVariant::all()->map(fn(\App\Models\ProductVariant $variant) => [
                'id' => $variant->id,
                'text' => $variant->getVariantName(withProductName: true),
            ])->toArray()
        ];
    }
};
?>




<div>
    <x-header title="Satın Alma Siparişi Ekle" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-2 gap-6">
            <div class="col-span-1 gap-3 grid">
                <x-input wire:model="purchase_no" label="Satın Alma No" readonly/>

                <x-choices-offline searchable single label="Tedarikçi" wire:model="supplier_id" :options="$suppliers"
                                   required style="height: 45px"/>

                <x-select searchable placeholder="Seçiniz" single label="Para Birimi"
                          :options="$currencies" wire:model="currency_id"
                          @change="$dispatch('currency-changed', [$(`#${$event.target.id} :selected`).text()])"
                          required style="height: 45px"/>

                <x-datetime label="Alım Tarihi" wire:model="purchased_at" required type="date"/>
                <x-datetime label="Sipariş Termin Süresi" wire:model="deadline_at" required type="date"/>

                <x-image-library
                    crop-title-text="Görüntüyü Biçimlendir"
                    add-files-text="Görüntü Yükle"
                    change-text="Değiştir"
                    remove-text="Kaldır"
                    crop-text="Biçimlendir"
                    crop-cancel-text="Vazgeç"
                    crop-save-text="Kaydet"
                    wire:model="images"
                    wire:library="library"
                    :preview="$library"
                    label="Foto"
                    hint="Maks. 3MB"/>

            </div>

            <div class="col-span-1 gap-3 grid">
                <x-input label="Kaynak Belge" wire:model="source_doc"/>
                <x-choices-offline searchable single :options="$warehouses" label="Teslimat Deposu"
                                   wire:model="warehouse_id" required style="height: 45px"/>

                <x-input label="Fatura No" wire:model="invoice_no"/>
                <x-input label="İrsaliye No" wire:model="dispatch_no"/>

                <x-textarea rows="5" label="Notlar" wire:model="notes"/>
            </div>

            <div class="col-span-2">
                <hr class="my-5"/>

                <livewire:purchase.purchase-repeater :has-receipt="false"/>

            </div>
        </div>

        <x-slot:actions>

            <x-button label="Cancel" link="/purchases"/>

            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
