<?php

use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;

    public \App\Models\Shipment $shipment;
    public \App\Models\Sale $sale;

    public ?array $shipments = [];
    public bool $hasMissing = false;
    public bool $showHistoryDrawer = false;

    public bool $myModal1 = false;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?bool $can_printable = false;


    public function mount()
    {
        $this->sale = $this->shipment->sale;

        /** @var \App\Models\ShipmentItem $item */
        if ($this->shipment->items) {
            foreach ($this->shipment->items as $item) {

                $this->shipments[] = [
                    'id'=> $this->shipment->id,
                    'product_id' => $item->product_id,
                    'sale_variant_id' => $item->sale_variant_id,
                    'variant_id' => $item->variant_id,
                    'qty' => $item->shipped_qty,
                    'sold_qty' => $item->sale_variant_qty,
                    'can_printable' => $item->can_printable,
                    'remain_qty' => '',
                    'shipment_address_id' => $item->delivery_address_id,
                ];

            }
        }

        $this->hasMissing = collect($this->shipments)->sum(fn(array $values) => (int)$values['qty']) < $this->sale->quantity;
    }

    #[\Livewire\Attributes\On('shipments-updated')]
    public function shipmentsUpdated($value)
    {
        $this->shipments = $value;
    }

    public function showMessageLogsDrawer()
    {
        $this->showMessageLogsDrawer = true;
    }

    public function save(): void
    {
        foreach ($this->shipments as $item) {
            if (empty($item['qty']) || empty($item['shipment_address_id'])) {
                throw \Mary\Exceptions\ToastException::error('Lütfen tüm alanları doldurunuz!');
            }
        }


        \App\Models\ShipmentItem::where('shipment_id', $this->shipment->id)->delete();

        foreach ($this->shipments as $item) {

            \App\Models\ShipmentItem::create([

                'shipment_id' => $this->shipment->id,
                'sale_id' => $this->sale->id,
                'sale_variant_qty' => $item['sold_qty'],
                'sale_variant_id' => $item['sale_variant_id'],
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'can_printable' => $item['can_printable'] ?? false,
                'shipped_qty' => $item['qty'],
                'delivery_address_id' => $item['shipment_address_id'],

            ]);
        }



        $this->hasMissing = collect($this->shipments)->sum(fn(array $values) => (int)$values['qty']) < $this->sale->quantity;

        $this->shipment->update([
            'status' => $this->hasMissing
                ? \App\Enums\Shipment\ShipmentStatus::MISSING
                : \App\Enums\Shipment\ShipmentStatus::SHIPPED,

        ]);

        if ($this->hasMissing) {
            $status = \App\Enums\Shipment\ShipmentStatus::MISSING;
            $message = 'Sevkiyat detayları güncellendi';
        } else {
            $status = \App\Enums\Shipment\ShipmentStatus::SHIPPED;
            $message = 'Sevkiyat tamamlandı';
        }

        $this->shipment->update([
            'status' => $status
        ]);

        log_action(message: $message, relationType: 'Shipment', relationId: $this->shipment->id);

        $this->success($message, redirectTo: "/shipments/{$this->shipment->id}");
    }

    public function with(): array
    {
        return [


        ];
    }
};
?>


<div>

    <style>


     .modal-box {
            max-width: 1500px;
        }


    </style>


    <livewire:action-log key="{{ Str::random() }}" relation-type="Shipment" :relation-id="$shipment->id"
                         :show-history-drawer="$showHistoryDrawer"/>

    <livewire:message-log id="messageHistoryModal" redirect-to='{{ "/shipments/{$shipment->id}" }}'
                          relation-type="Shipment"
                          :relation-id="$shipment->id"/>

    <x-modal wire:model="myModal1" class="">
        <div class="mb-5">
            <livewire:shipment.shipment-waybil :shipments="$shipments" :sale-variants="$sale->variants"
                                               :sale="$sale"/>

        </div>


        <a class="btn normal-case btn-error text-white hover:text-cyan-200" @click="$wire.myModal1 = false">
            <x-icon name="o-x-mark"/> İptal
        </a>

        <a target="_blank" href="{{ "/shipments/{$shipment['id']}/print" }}" class="btn normal-case btn-success text-white hover:text-cyan-200">
        <x-icon name="o-printer"/> PDF Olarak Göster
        </a>
    </x-modal>

    <x-header title="Sevkiyat" subtitle="DÜzenle">
        <x-slot:middle class="!justify-end">
            <div>

            </div>
        </x-slot:middle>

        <x-slot:actions>
            <div>
                <x-button label="Yazdır" class="btn-success text-white hover:text-cyan-200"
                          @click="$wire.myModal1 = true"/>

                <x-button style="pointer-events: auto; opacity: 1;" icon="o-film" label="İşlem Geçmişi"
                          class="btn-warning"
                          wire:click="$set('showHistoryDrawer',true)"/>

                <x-button style="pointer-events: auto; opacity: 1;" icon="o-envelope" label="Mesaj Geçmişi"
                          class="btn-warning"
                          @click="messageHistoryModal.showModal()"/>


            </div>
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-1 gap-6">
            <div class="container mx-auto px-4 py-8">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <h2 class="text-xl font-bold p-1 bg-gray-50 border-b border-gray-200 text-gray-800 text-center">
                        Sipariş Detayları</h2>

                    <div class="flex flex-col lg:flex-row">
                        <!-- Sipariş Bilgileri -->
                        <div
                            class="w-full lg:w-1/2 p-5 border-b lg:border-b-0 lg:border-r border-gray-200 bg-white rounded-lg shadow-sm">
                            <h3 class="text-lg font-bold mb-3 text-gray-800 border-b pb-2">Sipariş Bilgileri</h3>

                            <div class="space-y-3 text-sm">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-600 w-36">Sevkiyat Durumu:</span>
                                    <span class="text-gray-800">{!! $shipment->status->textWithBadge() !!}</span>
                                </div>

                                <div class="flex items-center">
                                    <span class="font-medium text-gray-600 w-36">Sipariş No:</span>
                                    <span class="text-gray-800 font-semibold">{{ $sale->sales_no }}</span>
                                </div>

                                <div class="flex items-center">
                                    <span class="font-medium text-gray-600 w-36">Firma:</span>
                                    <span class="text-gray-800">{{ $sale->contact->name }}</span>
                                </div>

                                <div class="flex items-center">
                                    <span class="font-medium text-gray-600 w-36">Toplam Miktar:</span>
                                    <span class="text-gray-800">{{ number_format($shipment->sale->quantity) }}</span>
                                </div>


                                <div class="flex items-center">
                                    <span class="font-medium text-gray-600 w-36">Gönderilen Miktar:</span>
                                    <span
                                        class="text-green-600 font-semibold">{{ number_format($shipment->items->sum('shipped_qty')) }}</span>
                                </div>

                                <div class="flex items-center">
                                    <span class="font-medium text-gray-600 w-36">Kalan Miktar:</span>
                                    <span
                                        class="text-red-600 font-semibold">{{ number_format($shipment->sale->quantity - $shipment->items->sum('shipped_qty')) }}</span>
                                </div>

                                <div class="mt-4">
                                    <span class="font-medium text-gray-600 block mb-1">Notlar:</span>
                                    <p class="text-gray-700 bg-gray-50 p-3 rounded-md leading-relaxed border border-gray-200 text-xs">
                                        {{ $sale->notes ?: 'Not bulunmamaktadır.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Ürün Listesi -->
                        <div class="w-full lg:w-1/2 p-4">
                            <h3 class="text-lg font-semibold mb-3 text-gray-800">Ürün Listesi</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                    <tr class="bg-gray-50">
                                        <th class="py-2 px-3 text-xs font-medium text-gray-600 border-b"></th>
                                        <th class="py-2 px-3 text-xs font-medium text-gray-600 border-b">Ürün Adı</th>
                                        <th class="py-2 px-3 text-xs font-medium text-gray-600 border-b">Toplam Satış
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                    @php
                                        /** @var \App\Models\SaleItem $saleItem */
                                    @endphp
                                    @foreach($sale->items as $saleItem)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-3 border-b">
                                                <span
                                                    class="text-blue-500 hover:text-blue-700">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M19 9l-7 7-7-7"></path>
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
                                                        <th class="py-1 px-2 text-xs font-medium text-gray-500">
                                                            Varyant
                                                        </th>
                                                        <th class="py-1 px-2 text-xs font-medium text-gray-500">Satış
                                                            Adedi
                                                        </th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php
                                                        /** @var \App\Models\SaleVariant $saleVariant */
                                                    @endphp
                                                    @foreach($saleItem->variants as $saleVariant)
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
                        </div>
                    </div>

                    <div class="p-4 border-t border-gray-200">
                        <livewire:shipment.shipment-repeater :shipments="$shipments" :sale-variants="$sale->variants"
                                                             :sale="$sale"/>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Liste" link="/shipments"/>

           <!-- <x-button :disabled="!$hasMissing" label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit"
                      class="btn-primary"/>
-->
            <x-button  label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit"
                      class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>

<script>

</script>
