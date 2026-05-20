<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;
    use Mary\Traits\WithMediaSync;

    public \App\Models\Sale $sale;
    public \Illuminate\Support\Collection $items;
    public ?array $allSelectedVariants;

    #[\Livewire\Attributes\Rule('required')]
    public string $sales_no;

    #[\Livewire\Attributes\Rule('required')]
    public string $contact_id;

    #[\Livewire\Attributes\Rule('required')]
    public ?string $currency_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $delivery_address_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $invoice_address_id;

    #[\Livewire\Attributes\Rule('required')]
    public string $deadline_at;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $price_list_id = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?bool $is_renewable = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $payment_condition_id = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?float $sub_total = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?float $total = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $notes = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?\Illuminate\Support\Collection $library;

    #[\Livewire\Attributes\Rule('required')]
    public int $status = 1;

    #[\Livewire\Attributes\Rule('required')]
    public ?int $cargo_type = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $cargo_provider = null;

    #[\Livewire\Attributes\Rule('required')]
    public string $created_by;

    #[\Livewire\Attributes\Rule('required')]
    public string $updated_by;

    /*****************************************************************/
    #[\Livewire\Attributes\Rule(['images.*' => 'image|max:1024'])]
    public array $images = [];

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $selectedProducts = null;

    public array $deliveryAddresses = [];
    public array $invoiceAddresses = [];
    public \App\Enums\Sale\SaleStatus $statusAsEnum;
    public string $currency = '';
    public bool $showHistoryDrawer = false;

    public bool $showMessageLogsDrawer = false;

    public bool $showApproveModal = false;

    public bool $showInStockModal = false;

    public bool $showCancelModal = false;

    public bool $showPrintModal = false;

    public ?\App\Models\PriceList $priceList = null;

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


    public function mount(): void
    {
        $sale = \App\Models\Sale::firstWhere('id', $this->sale->id);
        $sale->makeHidden('library');

        $this->fill($sale);

        $this->priceList = \App\Models\PriceList::find($this->price_list_id);

        $this->deadline_at = date('Y-m-d', $sale->deadline_at->getTimestamp());

        $this->items = $sale->items;

        /**
         * @var int $index
         * @var \App\Models\SaleItem $item
         */
        foreach ($this->items as $index => $item) {
            $this->items[$index]['variants'] = $item->selected_variants;
        }

        $this->allSelectedVariants = $sale->selected_variants;

        $this->library = $this->sale->library ?? collect();
        $this->statusAsEnum = $this->sale->status;

        $this->currency = \App\Models\Currency::findOrFail($this->currency_id)->name;

        $this->updatedContactId($this->sale->contact_id);
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

    public function cancel(): void
    {
        if (!$this->statusAsEnum->editable()) {
            throw \Mary\Exceptions\ToastException::error('Sadece taslak siparişler iptal edilebilir!');
        }

        $this->sale->update([
            'status' => \App\Enums\Sale\SaleStatus::CANCELLED
        ]);

        log_action(message: 'Satış siparişi iptal edildi', relationType: 'Sale', relationId: $this->sale->id);

        $this->success('Satış siparişi iptal edildi.', redirectTo: '/sales');
    }


    public function approve()
    {
        if (!$this->statusAsEnum->editable()) {
            throw \Mary\Exceptions\ToastException::error('Bu siparişi onay için uygun değil!');
        }

        $this->sale->update([
            'status' => \App\Enums\Sale\SaleStatus::PENDING
        ]);

        log_action(message: 'Satış siparişi onaylandı', relationType: 'Sale', relationId: $this->sale->id);

        $this->success('Satış siparişi onaylandı.', redirectTo: '/sales');
    }

    public function approveInStock()
    {
        if ($this->statusAsEnum !== \App\Enums\Sale\SaleStatus::PENDING) {
            throw \Mary\Exceptions\ToastException::error('Bu satış siparişi onay için uygun değil!');
        }

        $shipment = \App\Models\Shipment::create([
            'sale_id' => $this->sale->id,
            'contact_id' => $this->sale->contact->id,
            'status' => \App\Enums\Shipment\ShipmentStatus::PENDING,
            'shipment_no' => generate_shipment_no(),
        ]);

        $this->sale->update([
            'status' => \App\Enums\Sale\SaleStatus::SOLD,
            'shipment_id' => $shipment->id,
        ]);

        /** @var \App\Models\SaleItem $item */
        /** @var \App\Models\SaleVariant $variant */
        if ($this->sale->status->sold()) {
            foreach ($this->sale->variants as $variant) {
                \App\Models\ProductTransaction::create([
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->variant_id,
                    'quantity' => $variant->qty,
                    'type' => \App\Enums\StockProcessType::OUT,
                    'relation_type' => \App\Enums\ProductStock\RelationType::SALE,
                    'relation_id' => $variant->sale_id,
                    'contact_id' => $this->contact_id,
                    'warehouse_id' => $variant->product->warehouse_id,
                    'created_by' => auth('web')->id(),
                ]);

                $variant->variant->decrement('stock', $variant->qty);

                $model = \App\Models\Inventory::firstOrCreate([
                    'warehouse_id' => $variant->product->warehouse_id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->variant_id,
                ],
                    [
                        'created_by' => auth('web')->id(),
                        'updated_by' => auth('web')->id(),
                    ]);

                $model->decrement('quantity', $variant->qty);
            }
        }

        log_action(message: 'Satış siparişi onaylandı ve ürünler stoktan düşürüldü', relationType: 'Sale', relationId: $this->sale->id);

        $this->success('Satış siparişi onaylandı ve ürünler stokta düşürüldü.', redirectTo: '/sales');
    }

    public function showMessageLogsDrawer()
    {
        $this->showMessageLogsDrawer = true;
    }

    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $validated = $this->validate();

        $data = collect($validated)->except(['selectedProducts', 'images']);

        $itemList = [];
        $salesId = $this->sale->id;
        $data['id'] = $salesId;

        $items = $this->selectedProducts;

        $allVariants = collect($items)->map(fn(array $item) => $item['variants'])->toArray();

        $data['selected_variants'] = $allVariants;

        if (empty($items)) {
            throw \Mary\Exceptions\ToastException::error('Lütfen ürün girişi yapınız!');
        }

        foreach ($items as $item) {
            if (empty($item['line_total'])) {
                throw \Mary\Exceptions\ToastException::error('Eksik alanları tamamlayın!');
            }
        }

        foreach ($items as $key => $item) {
            if (empty($item['product_id'])) {
                $this->error('Ürün seçiniz!');
                return;
            }

            $itemList[] = [
                'id' => Str::uuid(),
                'sale_id' => $salesId,

                'product_id' => $item['product_id'],
                'receipt' => $item['receipt'],
                'notes' => $item['notes'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
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

        $this->sale->update(
            to_case(collect($data)->except('products', 'images')->toArray())
        );

        \App\Models\SaleItem::where('sale_id', $this->sale->id)->delete();

        \App\Models\SaleItem::insert($itemList);

        \App\Models\SaleVariant::where('sale_id', $this->sale->id)->delete();

        \App\Models\SaleVariant::insert($variantList);

        $this->syncMedia(model: $this->sale, files: 'images', storage_subpath: 'sales');

        $this->success('Satış siparişi düzenlendi.', redirectTo: '/sales');

        log_action(message: 'Satış siparişi düzenlendi', relationType: 'Sale', relationId: $this->sale->id);

        $this->reset();
    }

    public function with(): array
    {
        return [
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

    public function print(int $type)
    {
        to_route('sales.print', ['id' => $this->sale->id, 'type' => $type]);
    }


};
?>

<div class="bg-gray-100 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-8xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Satış Siparişi Düzenle</h1>

        <x-header title="Satış Siparişi" subtitle="Düzenle">
            <x-slot:middle class="!justify-end">
                <x-steps wire:model="status">
                    @foreach(\App\Enums\Sale\SaleStatus::localize([\App\Enums\Sale\SaleStatus::CANCELLED]) as $status)
                        <x-step step="1" text="{{ $status }}"/>
                    @endforeach
                </x-steps>
            </x-slot:middle>

            <x-slot:actions>
                <x-dropdown label="İşlemler" class="btn-outline">
                    <x-menu-item title="Yazdır" icon="o-printer" @click="$wire.showPrintModal = true"/>
                    @if ($statusAsEnum->editable())
                        <x-menu-item title="Onayla" icon="o-check" @click="$wire.showApproveModal = true"/>
                        <x-menu-item title="İptal Et" icon="o-trash" @click="$wire.showCancelModal = true"/>
                    @endif
                    @if ($statusAsEnum === App\Enums\Sale\SaleStatus::PENDING)
                        <x-menu-item title="Onayla ve Stoktan Düş" icon="o-check"
                                     @click="$wire.showInStockModal = true"/>
                    @endif
                    <x-menu-separator/>
                    <x-menu-item title="İşlem Geçmişi" icon="o-film" wire:click="$set('showHistoryDrawer', true)"/>
                    <x-menu-item title="Mesaj Geçmişi" icon="o-envelope" @click="messageHistoryModal.showModal()"/>
                </x-dropdown>
            </x-slot:actions>
        </x-header>

        @if ($statusAsEnum->notEditable() || $sale->hasReturn())
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                @if ($statusAsEnum->notEditable() && $sale->hasReturn())
                    <p>Bu sipariş onaylanmış ve <a href="/sale-returns/{{ $sale->id }}/edit" class="underline">iade
                            işlemi</a> gerçekleştirilmiştir. Sadece belirli alanlar görüntülenebilir, düzenleme
                        yapılamaz.</p>
                @elseif ($statusAsEnum->notEditable())
                    <p>Bu sipariş onaylanmıştır. Sadece belirli alanlarda sınırlı düzenleme yapılabilir.</p>
                @else
                    <p>Bu sipariş için <a href="/sale-returns/{{ $sale->id }}/edit" class="underline">iade işlemi</a>
                        gerçekleştirilmiştir. İade işlemini görüntülemek için tıklayın.</p>
                @endif
            </div>
        @endif

        <x-form wire:submit="save" class="space-y-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Temel Bilgiler</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-input wire:model="sales_no" label="Satış No" readonly/>
                    <x-choices-offline searchable single label="Müşteri" wire:model.live="contact_id"
                                       :options="$contacts" :readonly="$statusAsEnum->readonly()" required/>
                    <x-select searchable placeholder="Seçiniz" single label="Para Birimi" :options="$currencies"
                              @change="$dispatch('currency-changed', [$(`#${$event.target.id} :selected`).text()])"
                              wire:model="currency_id" :readonly="$statusAsEnum->readonly()" required/>
                    <x-datetime label="Geçerlilik Tarihi" wire:model="deadline_at" :readonly="$statusAsEnum->readonly()"
                                required type="date"/>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Adres Bilgileri</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-choices-offline searchable single label="Teslim Adresi" wire:model="delivery_address_id"
                                       :options="$deliveryAddresses" :readonly="$statusAsEnum->readonly()" required/>
                    <x-choices-offline searchable single label="Fatura Adresi" wire:model="invoice_address_id"
                                       :options="$invoiceAddresses" :readonly="$statusAsEnum->readonly()" required/>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ödeme ve Kargo Bilgileri</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-choices-offline searchable single :options="$priceLists" label="Fiyat Listesi"
                                       wire:model="price_list_id" :readonly="$statusAsEnum->readonly()"/>
                    <x-choices-offline searchable single :options="[]" label="Ödeme Koşulları"
                                       wire:model="payment_condition_id" :readonly="$statusAsEnum->readonly()"/>
                    <x-radio label="Ödeme Yöntemi" :options="\App\Enums\Proposal\CargoType::listForMaryUI()"
                             wire:model="cargo_type"/>
                    <x-choices-offline icon="o-rocket-launch" searchable label="Kargo" wire:model="cargo_provider"
                                       :options="$cargoProviders" single/>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ek Bilgiler</h2>
                <div class="grid gap-6">
                    <x-toggle label="Yenileme" wire:model="is_renewable" :disabled="$statusAsEnum->readonly()"/>
                    <x-textarea rows="5" label="Notlar" wire:model="notes"/>
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
                        label="Fotoğraflar"
                        hint="Maks. 3MB"
                    />
                </div>
            </div>

            <!-- Ürün Listesi -->
            <x-card title="Ürün Listesi" class="mt-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                        <tr class="bg-gray-50">
                            <th class="py-2 px-3 text-xs font-medium text-gray-600 border-b"></th>
                            <th class="py-2 px-3 text-xs font-medium text-gray-600 border-b">Ürün Adı</th>
                            <th class="py-2 px-3 text-xs font-medium text-gray-600 border-b">Toplam Satış</th>
                        </tr>
                        </thead>
                        <tbody class="text-sm">
                        @foreach($items as $saleItem)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 border-b">
                                        <span class="text-blue-500 hover:text-blue-700">
                                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                </td>
                                <td class="py-2 px-3 border-b">{{ $saleItem->product->name }}</td>
                                <td class="py-2 px-3 border-b">{{ $saleItem->qty }}</td>
                            </tr>
                            <tr class="bg-gray-100">
                                <td colspan="3" class="py-2 px-3 border-b">
                                    <table class="w-full text-left">
                                        <thead>
                                        <tr>
                                            <th class="py-1 px-2 text-xs font-medium text-gray-500">Varyant</th>
                                            <th class="py-1 px-2 text-xs font-medium text-gray-500">Satış
                                                Miktarı
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($saleItem->variants()->get() as $saleVariant)
                                            <tr>
                                                <td class="py-1 px-2">{{ $saleVariant->variant->getVariantName() }}</td>
                                                <td class="py-1 px-2">{{ $saleVariant->qty }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>

            <div class="bg-white shadow rounded-lg p-6"
                 @if ($statusAsEnum->readonly()) style="pointer-events: none; opacity: 0.4;" @endif>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ürün Detayları</h2>
                <livewire:sale.sale-repeater
                    :has-receipt="true"
                    :is-edit="true"
                    :items="$items"
                    :currency-text="$currency"
                    :price-list="$priceList"
                />
            </div>

            <div class="flex justify-end space-x-4 mt-8">
                <x-button label="Vazgeç" link="/sales" class="bg-gray-200 hover:bg-gray-300 text-gray-800"/>
                <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit"
                          class="bg-blue-600 hover:bg-blue-700 text-white"/>
            </div>
        </x-form>
    </div>


    <!-- Modals -->
    <x-modal wire:model="showPrintModal" title="Teklif">
        <div>Teklif yazdırma seçeneği?</div>
        <x-slot:actions>
            <x-button label="Fiyatlı" wire:click="print(1)"/>
            <x-button label="Fiyatsız" wire:click="print(2)" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showApproveModal" title="Sipariş Onayı">
        <div>Bu sipariş onaylanacak. Emin misiniz?</div>
        <x-slot:actions>
            <x-button label="Hayır" @click="$wire.showApproveModal = false"/>
            <x-button label="Evet" wire:click="approve" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showInStockModal" title="Sipariş Onayı">
        <div>Bu sipariş onaylanacak ve ürünler stoğa alınacak. Emin misiniz?</div>
        <x-slot:actions>
            <x-button label="Hayır" @click="$wire.showInStockModal = false"/>
            <x-button label="Evet" wire:click="approveInStock" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showCancelModal" title="Satın Alma Onayı">
        <div>Bu satış işlemi iptal edilecek. Onaylıyor musunuz?</div>
        <x-slot:actions>
            <x-button label="Hayır" @click="$wire.showCancelModal = false"/>
            <x-button label="Evet" wire:click="cancel" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <livewire:action-log key="{{ Str::random() }}" relation-type="Sale" :relation-id="$sale->id"
                         :show-history-drawer="$showHistoryDrawer"/>

    <livewire:message-log id="messageHistoryModal" redirect-to='{{ "/sales/{$sale->id}/edit" }}' relation-type="Sale"
                          :relation-id="$sale->id"/>

</div>
