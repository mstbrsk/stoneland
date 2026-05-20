<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Illuminate\Support\Str;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;
    use Mary\Traits\WithMediaSync;

    public \App\Models\Purchase $purchase;
    public \Illuminate\Support\Collection $warehouses;


    public \Illuminate\Support\Collection $items;
    public ?array $allSelectedVariants = [];
    public $groupedVariants = [];

    public  $combinedItems = [];

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
    public ?string $source_doc;

    #[\Livewire\Attributes\Rule('required')]
    public int $warehouse_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $invoice_no = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $dispatch_no = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $notes = null;

    #[\Livewire\Attributes\Rule(['images.*' => 'image|max:3096'])]
    public array $images = [];

    #[\Livewire\Attributes\Rule('required')]
    public int $status = 1;

    #[\Livewire\Attributes\Rule('required')]
    public string $created_by;

    #[\Livewire\Attributes\Rule('required')]
    public string $updated_by;

    public bool $showPrintModal = false;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $selectedProducts = null;

    public bool $showApproveModal = false;
    public bool $showInStockModal = false;
    public bool $showCancelModal = false;
    public bool $historyDrawer = false;

    public string $currency = '';

    public ?int $newStatus = null;
    public \App\Enums\Purchase\PurchaseStatus $statusAsEnum;

    #[\Livewire\Attributes\Rule('sometimes')]
    public \Illuminate\Support\Collection $library;

    #[On('raise-selected-products')]
    public function setSelectedProducts(?array $products = null)
    {
        $this->selectedProducts = $products;
    }

    #[On('raise-all-selected-variants')]
    public function setSelectedVariants(?array $variants = null)
    {
        $this->allSelectedVariants = $variants;

    }

    public function mount()
    {
        $purchase = \App\Models\Purchase::firstWhere('id', $this->purchase->id);
        $purchase->makeHidden('library');
        $this->warehouses = \App\Models\Warehouse::all();
        $this->fill($purchase);

        $this->purchased_at = date('Y-m-d', $purchase->purchased_at->getTimestamp());
        $this->deadline_at = date('Y-m-d', $purchase->deadline_at->getTimestamp());

        $this->items = $purchase->items;

        foreach ($this->items as $index => $item) {
            $this->items[$index]['variants'] = $item->selected_variants;

        }

        $this->groupedVariants = collect($this->allSelectedVariants)
            ->flatMap(function ($variants) {
                return $variants;
            })
            ->groupBy(function ($qty, $variantId) {
                return $variantId;
            })
            ->map(function ($quantities) {
                return array_sum($quantities->toArray());
            });

        $this->allSelectedVariants = $purchase->selected_variants;


        $this->library = $this->purchase->library;
        $this->statusAsEnum = $purchase->status;

        $this->currency = \App\Models\Currency::findOrFail($this->currency_id)->name;



        $this->combinedItems = collect($this->items)->map(function ($item) {
            return [
                'product' => $item['product'],
                'variants' => $item['variants']
            ];
        })->toArray();







    }



    public function showHistoryDrawer()
    {
        $this->historyDrawer = true;
    }

    public function updated($field, $value)
    {
        if (in_array($field, ['invoice_no', 'dispatch_no'])) {
            // Özel işlemler buraya eklenebilir
        }
    }

    public function save(bool $redirect = true): void
    {
        $this->updated_by = auth('web')->id();

        $validated = $this->validate();

        $data = collect($validated)->except(['selectedProducts', 'items', 'purchase', 'images']);

        $purchaseId = $this->purchase->id;

        $itemList = [];
        $variantList = [];
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

        $allVariants = collect($items)->map(fn(array $item) => $item['variants'])->toArray();



        $data['selected_items'] = $items;
        $data['selected_variants'] = $allVariants;

        foreach ($items as $key => $item) {
            if (empty($item['product_id'])) {
                $this->error('Ürün seçiniz!');
                return;
            }

            $itemList[] = [
                'id' => Str::uuid()->toString(),
                'purchase_id' => $purchaseId,
                'product_id' => $item['product_id'],
                'notes' => $item['notes'] ?? null,
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'],
                'vat_line_total' => $item['vat_line_total'],
                'line_total' => $item['line_total'],
                'selected_variants' => json_encode($allVariants[$key]),
            ];

            foreach ($allVariants[$key] as $variantId => $qty) {
                if ($qty > 0) {
                    $variantList[] = [
                        'id' => Str::uuid()->toString(),
                        'purchase_id' => $purchaseId,
                        'purchase_item_id' => $itemList[count($itemList) - 1]['id'],
                        'product_id' => $item['product_id'],
                        'variant_id' => $variantId,
                        'qty' => $qty
                    ];
                }
            }
        }

        $data['sub_total'] = collect($items)->sum(fn(array $item) => $item['unit_price'] * $item['qty']);

        $data['total'] = collect($items)->sum(function (array $item) {
            $linePrice = $item['unit_price'] * $item['qty'];
            $vatAmount = $linePrice * ($item['vat_rate'] / 100);
            return $linePrice + $vatAmount;
        });

        $data['quantity'] = collect($items)->sum('qty');

        $this->purchase->update(
            to_case(collect($data)->except('products', 'images')->toArray())
        );

        \App\Models\PurchaseItem::where('purchase_id', $this->purchase->id)->delete();
        \App\Models\PurchaseItem::insert($itemList);

        \App\Models\PurchaseVariant::where('purchase_id', $this->purchase->id)->delete();
        \App\Models\PurchaseVariant::insert($variantList);

        $this->syncMedia(model: $this->purchase, files: 'images', storage_subpath: 'purchases');

        log_action(message: 'Satın alma siparişi güncellendi', relationType: 'Purchase', relationId: $this->purchase->id);

        $this->success('Satın alma siparişi güncellendi.', redirectTo: '/purchases');
    }

    public function updateStatus()
    {
        if ($this->newStatus < $this->status) {
            throw \Mary\Exceptions\ToastException::error('Durum bilgisi geriye dönük güncellenemez!');
        }

        $this->purchase->update([
            'status' => $this->newStatus
        ]);

        $this->success('Satın alma durumu güncellendi.', redirectTo: '/purchases');

        log_action(message: "Satın alma durumu {$this->newStatus} olarak güncellendi", relationType: 'Purchase', relationId: $this->purchase->id);
    }

    public function approve()
    {
        if ($this->statusAsEnum !== \App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL) {
            throw \Mary\Exceptions\ToastException::error('Bu satın alma siparişi onay için uygun değil!');
        }

        $this->save(redirect: false);

        $this->purchase->update([
            'status' => \App\Enums\Purchase\PurchaseStatus::PENDING
        ]);

        log_action(message: 'Satın alma siparişi onaylandı', relationType: 'Purchase', relationId: $this->purchase->id);

        $this->success('Satın alma siparişi onaylandı.', redirectTo: '/purchases');
    }

    public function approveInStock()
    {
        if ($this->statusAsEnum !== \App\Enums\Purchase\PurchaseStatus::PENDING) {
            throw \Mary\Exceptions\ToastException::error('Bu satın alma siparişi onay için uygun değil!');
        }

        $this->save(redirect: false);

        $this->purchase->update([
            'status' => \App\Enums\Purchase\PurchaseStatus::IN_STOCK
        ]);

        if ($this->purchase->status->isInStock()) {
            foreach ($this->purchase->variants as $variant) {
                \App\Models\ProductTransaction::create([
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->variant_id,
                    'quantity' => $variant->qty,
                    'type' => \App\Enums\StockProcessType::IN,
                    'relation_type' => \App\Enums\ProductStock\RelationType::PURCHASE,
                    'relation_id' => $variant->purchase_id,
                    'contact_id' => $this->supplier_id,
                    'warehouse_id' => $this->purchase->warehouse_id,
                    'created_by' => auth('web')->id(),
                ]);

                $variant->variant->increment('stock', $variant->qty);

                $model = \App\Models\Inventory::firstOrCreate([
                    'warehouse_id' => $this->purchase->warehouse_id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->variant_id,
                ],
                    [
                        'created_by' => auth('web')->id(),
                        'updated_by' => auth('web')->id(),
                    ]);

                $model->increment('quantity', $variant->qty);
            }
        }

        log_action(message: 'Satın alma siparişi onaylandı ve ürünler stoğa aktarıldı', relationType: 'Purchase', relationId: $this->purchase->id);

        $this->success('Satın alma siparişi onaylandı ve ürünler stoğa aktarıldı.', redirectTo: '/purchases');
    }

    public function isDisabled(): bool
    {
        if ($this->invoice_no) {
            return false;
        }

        if ($this->dispatch_no) {
            return false;
        }

        return true;
    }

    public function cancel()
    {
        if ($this->statusAsEnum !== \App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL) {
            throw \Mary\Exceptions\ToastException::error('Sadece onay bekleyen siparişler iptal edilebilir!');
        }

        $this->purchase->update([
            'status' => \App\Enums\Purchase\PurchaseStatus::CANCELLED
        ]);

        log_action(message: 'Satın alma siparişi iptal edildi', relationType: 'Purchase', relationId: $this->purchase->id);

        $this->success('Satın alma siparişi iptal edildi.', redirectTo: '/purchases');
    }

    public function with(): array
    {
        return [
            'allStatus' => \App\Enums\Purchase\PurchaseStatus::listForMaryUI([\App\Enums\Purchase\PurchaseStatus::CANCELLED]),
            'suppliers' => \App\Models\Contact::suppliers()->get(),
            'currencies' => \App\Models\Currency::get(),
            'warehouses' => \App\Models\Warehouse::all()->toArray(),
            'productList' => \App\Models\Product::all()->map(fn(\App\Models\Product $product) => [
                'id' => $product->id,
                'text' => $product->name,
            ])->toArray(),
            'isDisabled' => $this->isDisabled(),
        ];
    }

    public function print(int $type)
    {
        to_route('purchases.print', ['id' => $this->purchase->id, 'type' => $type]);
    }
};
?>

<div>

    <x-modal wire:model="showPrintModal" title="Satınalma" >
        <div>Satınalma yazdırma seçeneği?</div>
        <x-slot:actions>
            <x-button label="Fiyatlı" wire:click="print(1)"/>
            <x-button label="Fiyatsız" wire:click="print(2)" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showApproveModal" title="Satın Alma Onayı">
        <div>Bu satın alma onaylanacak.Emin misiniz?</div>
        <x-slot:actions>
            <x-button label="Hayır" @click="$wire.showApproveModal = false"/>
            <x-button label="Evet" wire:click="approve" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showInStockModal" title="Satın Alma Onayı">
        <div>Bu satın alma onaylanacak ve ürünler stoğa alınacak.Emin misiniz?</div>
        <x-slot:actions>
            <x-button label="Hayır" @click="$wire.showInStockModal = false"/>
            <x-button label="Evet" wire:click="approveInStock" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showCancelModal" title="Satın Alma Onayı">
        <div>Bu satın alma iptal edilecek.Onaylıyor musunuz?</div>
        <x-slot:actions>
            <x-button label="Hayır" @click="$wire.showCancelModal = false"/>
            <x-button label="Evet" wire:click="cancel" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <livewire:action-log key="{{ Str::random() }}" relation-type="Purchase" :relation-id="$purchase->id"
                         :show-history-drawer="$historyDrawer"/>

    <livewire:message-log id="messageHistoryModal" redirect-to='{{ "/purchases/{$purchase->id}/edit" }}'
                          relation-type="Purchase" :relation-id="$purchase->id"/>

    <x-form wire:submit="save">
        <x-header title="Satın Alma Siparişi" subtitle="Düzenle">
            <x-slot:middle class="!justify-end">
                <div>
                    <x-steps wire:model="status">
                        @foreach(\App\Enums\Purchase\PurchaseStatus::localize([\App\Enums\Purchase\PurchaseStatus::CANCELLED]) as $status)
                            <x-step step="1" text="{{ $status }}"/>
                        @endforeach
                    </x-steps>
                </div>
            </x-slot:middle>


            <x-slot:actions>
                <div>
                    @if ($statusAsEnum===\App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL)
                        <x-button type="button" label="Onayla" wire:confirm="Emin misiniz?"
                                  @click="$wire.showApproveModal = true"
                                  class="btn-secondary"
                                  icon="o-check"/>

                        <x-button type="button" label="İptal Et" wire:confirm="Emin misiniz?"
                                  @click="$wire.showCancelModal = true"
                                  class="btn-error"
                                  icon="o-trash"/>
                    @endif

                    @if ($statusAsEnum===App\Enums\Purchase\PurchaseStatus::PENDING)
                        <x-button type="button" label="Onayla ve Stoğa Al" wire:confirm="Emin misiniz?"
                                  @click="$wire.showInStockModal = true"
                                  :disabled="$isDisabled"
                                  class="btn-success"
                                  icon="o-check"/>
                    @endif



                </div>
            </x-slot:actions>



            <x-slot:actions>
                <div>
                    <x-dropdown label="İşlemler" class="btn-outline">
                        <x-menu-item title="Yazdır" icon="o-printer" @click="$wire.showPrintModal = true"/>

                        @if ($statusAsEnum===\App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL)
                            <x-button type="button" label="Onayla" wire:confirm="Emin misiniz?"
                                      @click="$wire.showApproveModal = true"
                                      class="btn-secondary"
                                      icon="o-check"/>

                            <x-button type="button" label="İptal Et" wire:confirm="Emin misiniz?"
                                      @click="$wire.showCancelModal = true"
                                      class="btn-error"
                                      icon="o-trash"/>
                        @endif

                        @if ($statusAsEnum===App\Enums\Purchase\PurchaseStatus::PENDING)
                            <x-button type="button" label="Onayla ve Stoğa Al" wire:confirm="Emin misiniz?"
                                      @click="$wire.showInStockModal = true"
                                      :disabled="$isDisabled"
                                      class="btn-success"
                                      icon="o-check"/>
                        @endif

                        <x-menu-separator/>

                        <x-menu-item title="İşlem Geçmişi" icon="o-film"  wire:click="showHistoryDrawer"/>

                        <x-menu-item title="Mesaj Geçmişi" icon="o-envelope"    @click="messageHistoryModal.showModal()"/>
                    </x-dropdown>
                </div>
            </x-slot:actions>



        </x-header>

        @if ($statusAsEnum->notEditable())
            <x-alert title="Sipariş onaylandığı için sadece belirli alanlarda düzenleme yapabilirsiniz!"
                     class="alert-warning"
                     icon="o-exclamation-triangle"/>
        @endif

        <div class="lg:grid grid-cols-2 gap-6">
            <x-card>

                <div class="col-span-1 gap-3 grid">
                    <x-input wire:model="purchase_no" label="Satın Alma No" readonly/>

                   <x-choices-offline
                        :readonly="$statusAsEnum!==\App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL"
                        searchable single label="Tedarikçi" wire:model="supplier_id" :options="$suppliers"
                        required style="height: 45px"/>

                    <x-select searchable placeholder="Seçiniz" single label="Para Birimi" class="cmb-currency"
                              :readonly="$statusAsEnum!==\App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL"
                              :options="$currencies" wire:model="currency_id"
                              @change="$dispatch('currency-changed', [$(`#${$event.target.id} :selected`).text()])"
                              required style="height: 45px"/>

                    <x-datetime label="Alım Tarihi" wire:model="purchased_at" required type="date"
                                :readonly="$statusAsEnum!==\App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL"/>

                    <x-datetime icon="o-calendar" label="Sipariş Termin Süresi" wire:model="deadline_at" required
                                :readonly="$statusAsEnum!==\App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL"
                                type="date"/>

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
            </x-card>

            <x-card>
                <div class="col-span-1 gap-3 grid">
                    <x-input label="Kaynak Belge" wire:model="source_doc"/>
                    <x-choices-offline searchable single :options="$warehouses" label="Teslimat Deposu"
                                       wire:model="warehouse_id" required style="height: 45px"/>

                    <x-input label="Fatura No" wire:model.live.debounce="invoice_no"/>
                    <x-input label="İrsaliye No" wire:model.live.debounce="dispatch_no"/>

                    <x-textarea rows="5" label="Notlar" wire:model="notes"/>
                </div>
            </x-card>




























            <div class="col-span-2">
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


                            @foreach($combinedItems as $item)
                                @php
                                    $variants = $item['variants'];
                                    $totalQty = array_sum($variants);
                                @endphp
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
                                    <td class="py-2 px-3 border-b">{{ $item['product']['name'] ?? 'Ürün adı bulunamadı' }}</td>
                                    <td class="py-2 px-3 border-b">{{ $totalQty }}</td>
                                </tr>
                                <tr class="bg-gray-100">
                                    <td colspan="3" class="py-2 px-3 border-b">
                                        <table class="w-full text-left">
                                            <thead>
                                            <tr>
                                                <th class="py-1 px-2 text-xs font-medium text-gray-500">Varyant</th>
                                                <th class="py-1 px-2 text-xs font-medium text-gray-500">Miktar</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($variants as $variantId => $qty)
                                                @if($qty > 0)
                                                    @php
                                                        $variant = App\Models\ProductVariant::find($variantId);
                                                    @endphp
                                                    @if($variant)
                                                        <tr>
                                                            <td class="py-1 px-2">{{ $variant->getVariantName() }}</td>
                                                            <td class="py-1 px-2">{{ $qty }}</td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td colspan="2" class="py-1 px-2 text-red-500">Varyant bulunamadı: {{ $variantId }}</td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="py-1 px-2">Bu ürün için varyant bulunamadı</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>






         {{-- <div class="col-span-2">
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

                            @foreach($items as $item)
                                @php
                                   $variants = collect($item['variants'])->toArray();
                                   $totalQty = array_sum($variants);
                                @endphp

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
                                    <td class="py-2 px-3 border-b">{{ $item['product']['name'] ?? 'Ürün adı bulunamadı' }}</td>
                                    <td class="py-2 px-3 border-b">{{ $totalQty }}</td>

                                </tr>
                                <tr class="bg-gray-100">
                                    <td colspan="3" class="py-2 px-3 border-b">
                                        <table class="w-full text-left">
                                            <thead>
                                            <tr>
                                                <th class="py-1 px-2 text-xs font-medium text-gray-500">Varyant</th>
                                                <th class="py-1 px-2 text-xs font-medium text-gray-500">Satış Miktarı</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            @foreach($variants as $variantId => $qty)

                                                @if($qty > 0 )
                                                    @php
                                                        $variant = App\Models\ProductVariant::find($variantId);

                                                    @endphp
                                                    @if($variant)
                                                        <tr>
                                                            <td class="py-1 px-2">{{ $variant->getVariantName() }}</td>
                                                            <td class="py-1 px-2">{{ $qty }}</td>
                                                        </tr>
                                                    @endif
                                                @endif
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
            </div>--}}








            <div class="col-span-2"
                 @if ($statusAsEnum!==\App\Enums\Purchase\PurchaseStatus::WAITING_FOR_APPROVAL)  style="pointer-events: none; opacity: 0.4;" @endif>
                <hr class="my-5"/>

                <livewire:purchase.purchase-repeater
                    :has-receipt="false"
                    :is-edit="true"
                    :items="$items"

                    :currency-text="$currency"/>
            </div>

        </div>

        <x-slot:actions>
            <x-button label="Vazgeç" link="/purchases"/>

            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>

</div>
