<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;
    use Mary\Traits\WithMediaSync;

    #[\Livewire\Attributes\Rule('required')]
    public string $sales_no;

    #[\Livewire\Attributes\Rule('required')]
    public string $contact_id;

    #[\Livewire\Attributes\Rule('required')]
    public ?string $currency_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public string $delivery_address_id = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public string $invoice_address_id = '';

    #[\Livewire\Attributes\Rule('required')]
    public string $deadline_at;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $price_list_id = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?bool $is_renewable = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?bool $has_receipt = false;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $payment_condition_id = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $notes = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public \Illuminate\Support\Collection $library;

    #[\Livewire\Attributes\Rule('required')]
    public int $status = 1;

    #[\Livewire\Attributes\Rule('required')]
    public int $cargo_type = \App\Enums\Proposal\CargoType::US->value;

    #[\Livewire\Attributes\Rule('sometimes')]
    public string $cargo_provider = '';

    #[\Livewire\Attributes\Rule('required')]
    public string $created_by;

    #[\Livewire\Attributes\Rule('required')]
    public string $updated_by;

    /*****************************************************************/
    #[\Livewire\Attributes\Rule(['images.*' => 'image|max:1024'])]
    public array $images = [];

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $selectedProducts = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $allSelectedVariants = null;

    public array $deliveryAddresses = [];
    public array $invoiceAddresses = [];

    public ?\App\Models\PriceList $priceList;

    /*****************************************************************/

    #[On('raise-selected-products')]
    public function setSelectedProducts(?array $products = null)
    {
        $this->selectedProducts = $products;
    }

    #[On('raise-discount-rate')]
    public function setDiscountRate($totalDiscountAmount)
    {
        $this->totalDiscountAmount = $totalDiscountAmount;
    }



    public function mount()
    {
        $this->fill([
            'library' => collect(),
            'sales_no' => generate_sales_no(),
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

    public function save(): void
    {
        $items = $this->selectedProducts;

        if (empty($items)) {
            throw \Mary\Exceptions\ToastException::error('Lütfen ürün girişi yapınız!');
        }

        foreach ($items as $item) {
            if (empty($item['line_total'])) {
                throw \Mary\Exceptions\ToastException::error('Eksik alanları tamamlayın!');
            }
        }


        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $validated = $this->validate();

        $data = collect($validated)->except(['selectedProducts', 'images', 'allSelectedVariants']);

        $allVariants = collect($items)->map(fn(array $item) => $item['variants'])->toArray();

        $data['selected_variants'] = $allVariants;



        $itemList = [];
        $variantList = [];

        $salesId = Str::uuid();
        $data['id'] = $salesId;

        $this->priceList = \App\Models\PriceList::find($this->price_list_id);

        $data['sub_total'] = collect($items)->sum(function (array $item) {
            if ($this->priceList) {
                $item['unit_price'] = \App\Models\PriceList::calculate($this->priceList, $item['unit_price']);
            }

            return $item['unit_price'] * $item['qty'];
        });

        $data['total'] = collect($items)->sum(function (array $item) {
            if ($this->priceList) {
                $item['unit_price'] = \App\Models\PriceList::calculate($this->priceList, $item['unit_price']);
            }

            $linePrice = $item['unit_price'] * $item['qty'];
            $vatAmount = $linePrice * ($item['vat_rate'] / 100);

            return $linePrice + $vatAmount;
        });

        $data['quantity'] = collect($items)->sum('qty');

        $sale = \App\Models\Sale::create(to_case(collect($data)->except(['products', 'images'])->toArray()));


        foreach ($items as $key => $item) {
            if (empty($item['product_id'])) {
                $this->error('Ürün seçiniz!');
                return;
            }

            $itemList[] = [
                'id' => Str::uuid(),
                'sale_id' => $salesId,

                'receipt' => $item['receipt'],
                'product_id' => $item['product_id'],
                'notes' => $item['notes'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'discount_rate' => $item['discount_rate'],
                'vat_rate' => $item['vat_rate'],
                'vat_line_total' => $item['vat_line_total'],
                'line_total' => $item['line_total'],
                'selected_variants' => json_encode($allVariants[$key]),
            ];
        }

        /** @var \App\Models\SaleVariant[] $allVariants */
        foreach ($itemList as $key => $item) {
            foreach ($allVariants[$key] as $variantId => $qty) {
                $variantList[] = [
                    'id' => Str::uuid(),
                    'sale_id' => $salesId,
                    'sale_item_id' => $item['id'],
                    'product_id' => $item['product_id'],
                    'variant_id' => $variantId,
                    'qty' => $qty
                ];
            }
        }

        \App\Models\SaleItem::insert($itemList);
        \App\Models\SaleVariant::insert($variantList);

        $this->syncMedia(model: $sale, files: 'images', storage_subpath: 'purchases');

        $this->reset();

        $this->success('Satış siparişi oluşturuldu.', redirectTo: '/sales');

        log_action(message: 'Satış siparişi oluşturuldu', relationType: 'Sale', relationId: $sale->id);
    }

    public function with(): array
    {
        return [
            'paymentConditions' => \App\Models\PaymentCondition::get(),

            'currencies' => \App\Models\Currency::get(),

            'contacts' => \App\Models\Contact::get(),

            'priceLists' => \App\Models\PriceList::get(),

            'productList' => \App\Models\Product::all()->map(fn(\App\Models\Product $product) => [
                'id' => $product->id,
                'text' => $product->name,
            ])->toArray(),

            'cargoProviders' => config('sap.cargo_providers'),
        ];
    }
};
?>

<div class="bg-gray-100 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-8xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Yeni Satış Siparişi Oluştur</h1>

        <x-form wire:submit.prevent="save" class="space-y-8">
            <!-- Temel Bilgiler -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Temel Bilgiler</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-input wire:model="sales_no" label="Satış Sipariş Numarası" readonly class="bg-gray-50"/>
                    <x-choices-offline wire:model.live="contact_id" :options="$contacts" label="Müşteri" placeholder="Müşteri seçin" searchable required single />
                    <x-select searchable placeholder="Para birimi seçin" single label="Para Birimi" icon="o-currency-dollar" :options="$currencies" wire:model="currency_id" required
                              @change="$dispatch('currency-changed', [$(`#${$event.target.id} :selected`).text()])"
                    />
                    <x-datetime wire:model="deadline_at" label="Geçerlilik Tarihi" required type="date" />
                </div>
            </div>

            <!-- Adres Bilgileri -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Adres Bilgileri</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-choices-offline wire:model="delivery_address_id" :options="$deliveryAddresses" label="Teslimat Adresi" placeholder="Teslimat adresi seçin" searchable required single />
                    <x-choices-offline wire:model="invoice_address_id" :options="$invoiceAddresses" label="Fatura Adresi" placeholder="Fatura adresi seçin" searchable required single />
                </div>
            </div>

            <!-- Ödeme ve Kargo Bilgileri -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ödeme ve Kargo Bilgileri</h2>
                <div class="grid gap-6">
                    <x-radio label="Ödeme Yöntemi" :options="\App\Enums\Proposal\CargoType::listForMaryUI()" wire:model="cargo_type" />
                    <x-choices-offline icon="o-truck" searchable label="Kargo Sağlayıcısı" wire:model="cargo_provider" :options="$cargoProviders" single />
                    <x-choices-offline wire:model.live="price_list_id" :options="$priceLists" label="Fiyat Listesi" placeholder="Fiyat listesi seçin" searchable single />
                    <x-choices-offline wire:model="payment_condition_id" :options="$paymentConditions" label="Ödeme Koşulları" placeholder="Ödeme koşulları seçin" searchable single />
                    <x-toggle wire:model="is_renewable" label="Yenilenebilir" />
                </div>
            </div>

            <!-- Ek Notlar -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ek Notlar</h2>
                <x-textarea wire:model="notes" rows="4" placeholder="Ek notları buraya girin..." />
            </div>

            <!-- Sipariş Görselleri -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Sipariş Görselleri</h2>
                <x-image-library
                    wire:model="images"
                    wire:library="library"
                    :preview="$library"
                    crop-title-text="Görseli Kırp"
                    add-files-text="Görsel Yükle"
                    change-text="Değiştir"
                    remove-text="Kaldır"
                    crop-text="Kırp"
                    crop-cancel-text="İptal"
                    crop-save-text="Kaydet"
                    label="Görseller"
                    hint="Görsel başına maks. 3MB"
                />
            </div>

            <!-- Ürün Detayları -->
            <div class="bg-white shadow rounded-lg p-6 w-full">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ürün Detayları</h2>
                <div class="overflow-x-auto">
                    <div class="min-w-full">
                        <livewire:sale.sale-repeater :has-receipt="true"/>
                    </div>
                </div>
            </div>

            <!-- İşlem Butonları -->
            <div class="flex justify-end space-x-4 mt-8">
                <x-button label="İptal" link="/sales" class="bg-gray-200 hover:bg-gray-300 text-gray-800" />
                <x-button label="Sipariş Oluştur" icon="o-paper-airplane" spinner="save" type="submit" class="bg-blue-600 hover:bg-blue-700 text-white" />
            </div>
        </x-form>
    </div>
</div>
