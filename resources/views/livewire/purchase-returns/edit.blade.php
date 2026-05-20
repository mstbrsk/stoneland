<?php
/** @var \App\Models\PurchaseItem[] $items */

/** @var \App\Models\PurchaseItem $purchaseItem */

/** @var \App\Models\PurchaseVariant $purchaseVariant */

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;

    public \App\Models\Purchase $purchase;
    public \Illuminate\Support\Collection $items;

    public bool $showMessageLogsDrawer = false;
    public bool $showHistoryDrawer = false;

    public ?string $currency_id;
    public ?string $price_list_id = null;

    public string $currency = '';
    public ?\App\Models\PriceList $priceList = null;

    public array $quantities = [];

    public ?string $sale_invoice_no = '';

    public function mount()
    {
        $purchase = $this->purchase;

        $this->sale_invoice_no = $purchase->sale_invoice_no;

        if (!$purchase->isInStock()) {
            $this->redirect("purchases/{$this->purchase->id}/edit");
        }

        $this->items = $purchase->items;

        $this->allSelectedVariants = $purchase->selected_variants;

        $this->statusAsEnum = $this->purchase->status;

        /** @var \App\Models\SaleVariant $saleVariant $saleVariant */
        foreach ($purchase->variants as $saleVariant) {
            $this->quantities[$saleVariant->variant_id] = '';
        }

        if ($this->purchase->hasReturn()) {
            foreach ($this->purchase->getSaleReturnVariantList() as $item) {
                $this->quantities[$item['variant_id']] = $item['qty'];
            }
        }
    }

    public function showMessageLogsDrawer()
    {
        $this->showMessageLogsDrawer = true;
    }

    public function save(): void
    {
        if ($this->purchase->hasReturn()) {
            if (empty($this->sale_invoice_no)) {
                $this->error('Satış iade fatura no alanı gerekli!');

                $this->dispatch('focus-on-return-invoice-no');
                return;
            }

            $this->purchase->purchaseReturn->update([
                'sale_invoice_no' => $this->sale_invoice_no,
            ]);

            $this->purchase->update([
                'sale_invoice_no' => $this->sale_invoice_no,
            ]);

            log_action(message: 'Satış iade fatura bilgisi girildi', relationType: 'PurchaseReturn', relationId: $this->purchase->purchaseReturn->id);

            $this->success('Satış iade fatura bilgisi girildi', redirectTo: '/purchase-returns');
        }

        $hasQty = collect($this->quantities)->contains(fn(mixed $qty) => (int)$qty > 0);

        if (!$hasQty) {
            $this->error('İade miktarı belirtilmedi!');
            return;
        }

        $returns = collect($this->quantities)->map(function (int|string|null $qty, string $variantId) {
            $purchaseVariants = $this->purchase->variants;

            /** @var \App\Models\PurchaseVariant $purchaseVariant */
            $purchaseVariant = $purchaseVariants->firstWhere('variant_id', $variantId);

            return [
                'item_id' => $purchaseVariant->id,
                'product_id' => $purchaseVariant->product_id,
                'warehouse_id' => $purchaseVariant->product->warehouse_id,
                'variant_id' => $purchaseVariant->variant_id,
                'qty' => (int)$qty,
            ];
        })
            ->values()
            ->toArray();

        $purchaseReturn = \App\Models\PurchaseReturn::create([
            'purchase_id' => $this->purchase->id,
            'status' => \App\Enums\Purchase\PurchaseReturnStatus::DONE,
            'returns' => $returns,
            'created_by' => auth('web')->id(),
            'updated_by' => auth('web')->id(),
            'sale_invoice_no' => $this->sale_invoice_no,
        ]);

        foreach ($returns as $item) {
            \App\Models\ProductTransaction::create([
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'quantity' => $item['qty'],
                'type' => \App\Enums\StockProcessType::OUT,
                'relation_type' => \App\Enums\ProductStock\RelationType::PURCHASE_RETURN,
                'relation_id' => $purchaseReturn->id,
                'contact_id' => $this->purchase->contact_id,
                'warehouse_id' => $item['warehouse_id'],
                'created_by' => auth('web')->id(),
            ]);

            $inventory = \App\Models\Inventory::create([
                'warehouse_id' => $item['warehouse_id'],
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'created_by' => auth('web')->id(),
                'updated_by' => auth('web')->id(),
            ]);

            $inventory->decrement('quantity', $item['qty']);
        }

        $this->purchase->update([
            'purchase_return_id' => $purchaseReturn->id,
            'sale_invoice_no' => $this->sale_invoice_no,
        ]);

        log_action(message: 'İade işlemi oluşturuldu', relationType: 'PurchaseReturn', relationId: $purchaseReturn->id);

        $this->success('İade işlemi oluşturuldu', redirectTo: '/purchase-returns');
    }

    public function updated($field, $value)
    {
        if (empty($value) || !str_contains($field, 'quantities')) {
            return;
        }

        [$_, $variantId] = explode('.', $field);
        $value = (int)$value;

        $variant = $this->purchase->variants->firstWhere('variant_id', $variantId);
        $maxQty = $variant->qty;

        $this->quantities[$variantId] = min($value, $maxQty);
    }

    public function incrementReturn($variantId)
    {
        if ($this->quantities[$variantId] < $this->purchase->variants->firstWhere('variant_id', $variantId)->qty) {
            $this->quantities[$variantId]++;
        }

        $this->updated("quantities.{$variantId}", $this->quantities[$variantId]);
    }

    public function decrementReturn($variantId)
    {
        if ($this->quantities[$variantId] > 0) {
            $this->quantities[$variantId]--;
        }

        $this->updated("quantities.{$variantId}", $this->quantities[$variantId]);
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
            ])->toArray()
        ];
    }
};
?>
<div class="max-w-7xl mx-auto p-8 bg-gray-100">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Satın Alma İade Formu</h2>

            <div class="flex space-x-3">
                @if ($purchase->hasReturn())
                    <button class="btn btn-secondary btn-outline" wire:click="$set('showHistoryDrawer',true)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        İşlem Geçmişi
                    </button>

                    <button class="btn btn-accent btn-outline" @click="messageHistoryModal.showModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Mesaj Geçmişi
                    </button>

                    <x-button link="/purchase-returns/{{ $purchase->purchase_return_id }}/print" class="btn btn-accent btn-outline" label="Yazdır" icon="o-printer"/>

                    <livewire:action-log key="{{ Str::random() }}" relation-type="PurchaseReturn"
                                         :relation-id="$purchase->purchaseReturn->id"
                                         :show-history-drawer="$showHistoryDrawer"/>

                    <livewire:message-log id="messageHistoryModal"
                                          redirect-to='{{ "/purchase-returns/{$purchase->id}/edit" }}'
                                          relation-type="PurchaseReturn"
                                          :relation-id="$purchase->purchaseReturn->id"/>
                @endif

                @if(!$purchase->hasReturn() || empty($purchase->sale_invoice_no))
                    <x-button spinner
                              class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                              wire:click="save"
                              icon="o-check"
                              class="btn-primary"
                    >
                        Kaydet
                    </x-button>
                @endif
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 mb-8 shadow-inner">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <span class="text-sm font-medium text-gray-600">Fatura No:</span>
                    <a href="/purchases/{{ $purchase->id }}/edit"
                       class="block mt-1 text-lg font-semibold text-blue-600 hover:underline">{{ $purchase->purchase_no }}</a>
                </div>

                <div>
                    <span class="text-sm font-medium text-gray-600">Müşteri:</span>
                    <a href="/contacts/{{ $purchase->supplier->id }}/edit"
                       class="block mt-1 text-lg font-semibold text-blue-600 hover:underline">{{ $purchase->supplier->name }}</a>
                </div>

                <div>
                    <span class="text-sm font-medium text-gray-600">Tarih:</span>
                    <span
                        class="block mt-1 text-lg font-semibold text-gray-800">{{ $purchase->created_at->format('d.m.Y') }}</span>
                </div>

                <div class="bg-base-200 p-4 rounded-lg shadow">
                    <label for="return_no" class="text-sm text-base-content opacity-70">Satış İade Fatura No</label>
                    <input type="text" wire:model="sale_invoice_no" id="txtReturnInvoiceNo"
                           class="input input-bordered w-full mt-1"
                           @if($purchase->hasReturn() && $purchase->sale_invoice_no) readonly @endif>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-xl overflow-hidden border border-gray-200"
            @if ($purchase->hasReturn()) style="pointer-events: none;opacity: 0.9;" @endif>
            @if($purchase->hasReturn())
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Bilgi</p>
                    Bu satın alma için daha önce bir iade işlemi gerçekleştirilmiştir. Mevcut iade detayları
                    görüntüleme modunda sunulmaktadır.
                </div>
            @endif

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Satış
                        Miktarı
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">İade
                        Miktarı
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($purchase->items as $purchaseItem)
                    <tr class="bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $purchaseItem->product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ $purchaseItem->qty }}</td>
                        <td></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500"></td>
                    </tr>
                    @foreach($purchaseItem->variants as $purchaseVariant)
                        @if($purchaseVariant->qty > 0)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 pl-12">
                                - {{ $purchaseVariant->variant->getVariantName() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ $purchaseVariant->qty }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center">
                                    <button
                                        class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700"
                                        wire:click.prevent="decrementReturn('{{ $purchaseVariant->variant_id }}')">
                                        <svg class="h-5 w-5" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                             stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                            <path d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <input
                                        type="number"
                                        wire:model.live.debounce="quantities.{{ $purchaseVariant->variant_id }}"
                                        class="mx-2 border text-center w-16 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                        min="1"
                                        max="{{ $purchaseVariant->qty }}"
                                    >
                                    <button
                                        class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700"
                                        wire:click.prevent="incrementReturn('{{ $purchaseVariant->variant_id }}')">
                                        <svg class="h-5 w-5" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                             stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                            <path d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 text-left">  @php
                                    $quantity = isset($quantities[$purchaseVariant->variant_id]) ? floatval($quantities[$purchaseVariant->variant_id]) : 0;
                                    $unitPrice = floatval($purchaseItem->unit_price);
                                    $total = $quantity * $unitPrice;
                                @endphp
                                {{ number_format($total, 2) }} ₺ </td>
                        </tr>
                        @endif
                    @endforeach
                @endforeach
                </tbody>
                <tfoot>
                <tr class="bg-gray-100 font-bold">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Genel Toplam</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">{{ $purchase->items->sum('qty') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($purchase->total, 2) }}
                        ₺
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

