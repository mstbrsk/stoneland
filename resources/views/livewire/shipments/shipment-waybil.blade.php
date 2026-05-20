<div class="bg-white shadow-lg rounded-2xl overflow-hidden "  >
    <div class="p-8">
        <h3 class="text-3xl font-bold mb-8 text-gray-800">Sevkiyat Detayları</h3>

        @if (empty($shipments))
            <div class="bg-blue-50 p-4 mb-8 rounded-xl shadow-sm flex items-center" role="alert">
                <svg class="w-6 h-6 text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-blue-700 font-medium">Henüz sevkiyat bilgisi girilmemiş.</p>
            </div>
        @endif

        @if (!empty($shipments))
            @if ($hasMissing)
                <div class="bg-yellow-50 p-4 mb-8 rounded-xl shadow-sm flex items-center" role="alert">
                    <svg class="w-6 h-6 text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <p class="font-bold text-yellow-700">Dikkat!</p>
                        <p class="text-yellow-700">Bazı ürünlerin gönderimi eksik. Lütfen kontrol ediniz.</p>
                    </div>
                </div>
            @else
                <div class="bg-green-50 p-4 mb-8 rounded-xl shadow-sm flex items-center" role="alert">
                    <svg class="w-6 h-6 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-bold text-green-700">Harika!</p>
                        <p class="text-green-700">Tüm ürünler gönderilmeye hazır.</p>
                    </div>
                </div>
            @endif
        @endif

        <div class="bg-white rounded-xl shadow-md">
            <table class="w-full text-left">
                <thead>
                <tr class="bg-gray-50">
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Varyant Adı</th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Satılan Miktar
                    </th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Gönderim Miktar
                    </th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kalan Miktar</th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sevkiyat Yeri
                    </th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                        x-show="$wire.hasMissing">Kargo
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">

                @foreach($shipments as $index => $shipment)

                    <tr class="hover:bg-gray-50 transition duration-150 ease-in-out {{ $shipment['disabled'] ? 'opacity-80 bg-gray-100' : '' }}"
                      >
                        <td class="py-4 px-6" style="width: 25%">
                            <div>
                                <x-choices-offline style="height: 44px" label="" :options="$saleVariantList"
                                                   searchable
                                                   single
                                                   :disabled="$shipment['disabled']"
                                                   wire:model.live.debounce="shipments.{{ $index }}.sale_variant_id"/>
                            </div>
                        </td>
                        <td class="py-4 px-6" style="width: 11%">
                            <input type="number" disabled wire:model="shipments.{{ $index }}.sold_qty"
                                   class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700">
                        </td>
                        <td class="py-4 px-6" style="width: 13%">
                            <input type="number" wire:model.lazy="shipments.{{ $index }}.qty"
                                   min="1"
                                   {{ $shipment['disabled'] ? "style=pointer-events:none;" : '' }}
                                   {{ $shipment['sale_variant_id'] === '' ? 'disabled' : '' }}
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700">
                        </td>
                        <td class="py-4 px-6" style="width: 11%">
                            <input type="number" disabled wire:model="shipments.{{ $index }}.remain_qty"
                                   class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700">
                        </td>
                        <td class="py-4 px-6" >
                            <x-choices-offline style="height: 44px" label="" :options="$deliveryAddresses"
                                               searchable
                                               single
                                               :disabled="$shipment['disabled']"
                                               wire:model.live.debounce="shipments.{{ $index }}.shipment_address_id"/>
                        </td>

                        <td class="py-4 px-6" > <x-choices-offline icon="o-rocket-launch" style="height: 45px" searchable
                                           wire:model="cargo_provider" :options="$mappedProviders" single/>
                        </td>
                    </tr>

                @endforeach
                </tbody>
            </table>
        </div>
    </div>




</div>
