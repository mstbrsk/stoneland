<?php
/** @var \App\Models\SaleItem[] $items */

/** @var \App\Models\SaleItem $saleItem */

/** @var \App\Models\SaleVariant $saleVariant */

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;

    public \App\Models\Sale $sale;
    public \Illuminate\Support\Collection $items;

    public bool $showMessageLogsDrawer = false;
    public bool $showHistoryDrawer = false;

    public ?string $currency_id;
    public ?string $price_list_id = null;

    public string $currency = '';
    public ?\App\Models\PriceList $priceList = null;

    public array $quantities = [];

    public ?string $return_invoice_no = '';

    public function mount()
    {
        $sale = $this->sale;

        $this->return_invoice_no = $sale->return_invoice_no;

        if (!$sale->isInStock()) {
            $this->redirect("sales/{$this->sale->id}/edit");
        }

        $this->items = $sale->items;

        $this->allSelectedVariants = $sale->selected_variants;

        $this->statusAsEnum = $this->sale->status;

        /** @var \App\Models\SaleVariant $saleVariant $saleVariant */
        foreach ($sale->variants as $saleVariant) {
            $this->quantities[$saleVariant->variant_id] = '';
        }

        if ($this->sale->hasReturn()) {
            foreach ($this->sale->getSaleReturnVariantList() as $item) {
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
        if ($this->sale->hasReturn()) {
            if (empty($this->return_invoice_no)) {
                $this->error('İade fatura no alanı gerekli!');

                $this->dispatch('focus-on-return-invoice-no');
                return;
            }

            $this->sale->saleReturn->update([
                'return_invoice_no' => $this->return_invoice_no,
            ]);

            $this->sale->update([
                'return_invoice_no' => $this->return_invoice_no,
            ]);

            log_action(message: 'İade fatura bilgisi girildi', relationType: 'SaleReturn', relationId: $this->sale->saleReturn->id);

            $this->success('İade fatura bilgisi girildi', redirectTo: '/sale-returns');
        }

        $hasQty = collect($this->quantities)->contains(fn(mixed $qty) => (int)$qty > 0);

        if (!$hasQty) {
            $this->error('İade miktarı belirtilmedi!');
            return;
        }

        $returns = collect($this->quantities)->map(function (int|string|null $qty, string $variantId) {
            $saleVariants = $this->sale->variants;

            /** @var \App\Models\SaleVariant $saleVariant */
            $saleVariant = $saleVariants->firstWhere('variant_id', $variantId);

            return [
                'item_id' => $saleVariant->sale_item_id,
                'product_id' => $saleVariant->product_id,
                'warehouse_id' => $saleVariant->product->warehouse_id,
                'variant_id' => $saleVariant->variant_id,
                'qty' => (int)$qty,
            ];
        })
            ->values()
            ->toArray();

        $saleReturn = \App\Models\SaleReturn::create([
            'sale_id' => $this->sale->id,
            'status' => \App\Enums\Sale\SaleReturnStatus::DONE,
            'returns' => $returns,
            'created_by' => auth('web')->id(),
            'updated_by' => auth('web')->id(),
            'return_invoice_no' => $this->return_invoice_no,
        ]);

        foreach ($returns as $item) {
            \App\Models\ProductTransaction::create([
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'quantity' => $item['qty'],
                'type' => \App\Enums\StockProcessType::IN,
                'relation_type' => \App\Enums\ProductStock\RelationType::SALE_RETURN,
                'relation_id' => $saleReturn->id,
                'contact_id' => $this->sale->contact_id,
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

            $inventory->increment('quantity', $item['qty']);
        }

        $this->sale->update([
            'sale_return_id' => $saleReturn->id,
            'return_invoice_no' => $this->return_invoice_no,
        ]);

        log_action(message: 'İade işlemi oluşturuldu', relationType: 'SaleReturn', relationId: $saleReturn->id);

        $this->success('İade işlemi oluşturuldu', redirectTo: '/sale-returns');
    }

    public function updated($field, $value)
    {
        if (empty($value) || !str_contains($field, 'quantities')) {
            return;
        }

        [$_, $variantId] = explode('.', $field);
        $value = (int)$value;

        $variant = $this->sale->variants->firstWhere('variant_id', $variantId);
        $maxQty = $variant->qty;

        $this->quantities[$variantId] = min($value, $maxQty);
    }

    public function incrementReturn($variantId)
    {
        if ($this->quantities[$variantId] < $this->sale->variants->firstWhere('variant_id', $variantId)->qty) {
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
<div class="max-w-7xl mx-auto p-6 bg-base-200">
    <div class="mb-8 bg-base-100 rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-primary">Satış İade Formu</h2>

            <div class="flex space-x-3">
                @if ($sale->hasReturn())
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

                    <livewire:action-log key="{{ Str::random() }}" relation-type="SaleReturn"
                                         :relation-id="$sale->saleReturn->id"
                                         :show-history-drawer="$showHistoryDrawer"/>

                    <livewire:message-log id="messageHistoryModal" redirect-to='{{ "/sale-returns/{$sale->id}/edit" }}'
                                          relation-type="SaleReturn"
                                          :relation-id="$sale->saleReturn->id"/>
                @endif

                @if(!$sale->hasReturn() || empty($sale->return_invoice_no))
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-base-200 p-4 rounded-lg shadow">
                <span class="text-sm text-base-content opacity-70">Fatura No</span>
                <a class="block text-lg font-semibold text-primary hover:text-primary-focus transition"
                   href="/sales/{{ $sale->id }}/edit">{{ $sale->sales_no }}</a>
            </div>

            <div class="bg-base-200 p-4 rounded-lg shadow">
                <span class="text-sm text-base-content opacity-70">Müşteri</span>
                <a class="block text-lg font-semibold text-primary hover:text-primary-focus transition"
                   href="/contacts/{{ $sale->contact_id }}/edit">{{ $sale->contact->name }}</a>
            </div>

            <div class="bg-base-200 p-4 rounded-lg shadow">
                <span class="text-sm text-base-content opacity-70">Tarih</span>
                <p class="text-lg font-semibold">{{ $sale->created_at->format('d.m.Y') }}</p>
            </div>

            <div class="bg-base-200 p-4 rounded-lg shadow">
                <label for="return_no" class="text-sm text-base-content opacity-70">İade Fatura No</label>
                <input type="text" wire:model="return_invoice_no" id="txtReturnInvoiceNo"
                       class="input input-bordered w-full mt-1"
                       @if($sale->hasReturn() && $sale->return_invoice_no) readonly @endif>
            </div>
        </div>
    </div>

    <div class="bg-base-100 rounded-lg shadow-lg overflow-hidden"
         @if ($sale->hasReturn()) style="pointer-events: none;opacity: 0.9;" @endif>
        @if($sale->hasReturn())
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                <p class="font-bold">Bilgi</p>
                <p>Bu satış faturası için daha önce bir iade işlemi gerçekleştirilmiştir. Mevcut iade detayları
                    görüntüleme modunda sunulmaktadır.</p>
            </div>
        @endif

        <table class="table w-full">
            <thead>
            <tr>
                <th class="text-left">Ürün</th>
                <th class="text-center">Satış Miktarı</th>
                <th class="text-center">İade Miktarı</th>
                <th class="text-right">Toplam</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sale->items as $saleItem)
                <tr class="bg-base-200">
                    <td class="font-medium">{{ $saleItem->product->name }}</td>
                    <td class="text-center">{{ $saleItem->qty }}</td>
                    <td class="text-right"></td>
                    <td class="text-right"></td>
                </tr>
                @foreach($saleItem->variants as $saleVariant)
                    <tr>
                        <td class="pl-8">- {{ $saleVariant->variant->getVariantName() }}</td>
                        <td class="text-center">{{ $saleVariant->qty }}</td>
                        <td>
                            <div class="flex items-center justify-center">
                                <button class="btn btn-square btn-sm btn-outline"
                                        wire:click.prevent="decrementReturn('{{ $saleVariant->variant_id }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M20 12H4"/>
                                    </svg>
                                </button>

                                <input type="number"
                                       wire:model.live.debounce="quantities.{{ $saleVariant->variant_id }}"
                                       class="input input-bordered input-sm w-20 mx-2 text-center"
                                       min="1" max="{{ $saleVariant->qty }}">
                                <button class="btn btn-square btn-sm btn-outline"
                                        wire:click.prevent="incrementReturn('{{ $saleVariant->variant_id }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                       <td> @php
                            $quantity = isset($quantities[$saleVariant->variant_id]) ? floatval($quantities[$saleVariant->variant_id]) : 0;
                            $unitPrice = floatval($saleItem->unit_price);
                            $total = $quantity * $unitPrice;
                        @endphp
                        {{ number_format($total, 2) }} ₺ </td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
            <tfoot>
            <tr class="bg-base-300 font-bold">
                <td>Genel Toplam</td>
                <td class="text-center">{{ $sale->items->sum('qty') }}</td>
                <td class="text-center"></td>
                <td class="text-right">{{ number_format($sale->total, 2) }} ₺</td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
