<div class="container mx-auto px-4 py-6">
    <div class="bg-white overflow-hidden  sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <x-modal wire:model.live="showVariantModal" title="Ürün Varyantları">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        /** @var \App\Models\ProductVariant[] $variants */
                    @endphp



                    @foreach($variants as $index => $variant)
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ $variant->getVariantName() }}</label>

                            <x-input type="number" wire:model="variantQuantities.{{ $index }}"
                                     :suffix="empty($variant->stock) ? ' 0 ' : $variant->stock"
                                     key="{{ Str::random() }}"
                                     class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"/>
                        </div>

                    @endforeach


                    <x-slot:actions>
                        <div class="flex justify-end space-x-2">
                            <x-button label="Kapat" @click="$wire.showVariantModal = false"
                                      class="bg-gray-300 hover:bg-gray-400 text-gray-800"/>
                            <x-button wire:click.prevent="saveVariantQuantities" label="Kaydet"
                                      class="bg-blue-500 hover:bg-blue-600 text-white"/>
                        </div>
                    </x-slot:actions>
                </div>
            </x-modal>

            <div class="overflow-x-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 table-auto border-collapse shadow-sm rounded-lg overflow-hidden">
                    <thead class="bg-gray-50">
                    <tr>
                        @if ($hasReceipt)
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reçete
                            </th>
                        @endif
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ürün
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Açıklama
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Miktar
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birim
                            Fiyat
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vergi
                            (%)
                        </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İskonto
                                (%)
                            </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vergi
                            Tutarı
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Toplam
                        </th>
                        <th class="px-3 py-2"></th>
                        <th class="px-3 py-2"></th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" x-data="xSelectedProducts">
                    @foreach ($items as $index => $item)
                        <tr>
                            @if($hasReceipt)
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <x-checkbox wire:model.lazy="items.{{ $index }}.receipt"/>
                                </td>
                            @endif

                            <td class="px-3 py-2 whitespace-nowrap">
                                <x-choices
                                    x-on:change="updateLine({{ $index }})"
                                    wire:model.live.debounce="items.{{ $index }}.product_id"
                                    :options="$products"
                                    single
                                    searchable
                                    search-function="search"
                                    class="shadow-sm border border-gray-300 rounded-md w-full py-1 px-2 text-sm text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                                    style="width: 150px; height: 32px"
                                />
                            </td>

                            <td class="px-3 py-2 whitespace-nowrap">
                                <x-textarea wire:model.lazy="items.{{ $index }}.notes" rows="2"
                                            class="shadow-sm border border-gray-300 rounded-md w-full py-1 px-2 text-sm text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"/>
                            </td>

                            <td class="px-3 py-2 whitespace-nowrap">
                                <input type="number" min="1" wire:model.lazy="items.{{ $index }}.qty"
                                       placeholder="Miktar"
                                       x-on:keyup="updateLine({{ $index }})"
                                       x-on:keydown="preventNegative"
                                       :disabled="true"
                                       class="shadow-sm border border-gray-300 rounded-md w-full py-1 px-2 text-sm text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                            </td>

                            <td class="px-3 py-2 whitespace-nowrap">
                                <input type="text" min="1" wire:model.lazy="items.{{ $index }}.formatted_price"
                                       placeholder="Birim Fiyat"
                                       x-on:keydown="updateLine({{ $index }})"
                                       x-on:keyup="formatPrice"
                                       class="shadow-sm border border-gray-300 rounded-md w-full py-1 px-2 text-sm text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                            </td>

                            <td class="px-3 py-2 whitespace-nowrap">
                                <input type="number" min="1" wire:model.lazy="items.{{ $index }}.vat_rate"
                                       placeholder="Vergi (%)"
                                       x-on:keyup="updateLine({{ $index }})"
                                       x-on:keydown="preventNegative"
                                       class="shadow-sm border border-gray-300 rounded-md w-full py-1 px-2 text-sm text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                            </td>

                                <td class="px-3 py-2 whitespace-nowrap">
                                    <input type="number" min="0" wire:model.lazy="items.{{ $index }}.discount_rate"
                                           placeholder="İskonto (%)"
                                           x-on:keyup="updateLine({{ $index }})"
                                           x-on:keydown="preventNegative"
                                           class="shadow-sm border border-gray-300 rounded-md w-full py-1 px-2 text-sm text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                                </td>

                            <td class="px-3 py-2 whitespace-nowrap text-sm">
                                {{ format_number($item['vat_line_total'],symbol: $currencyText) }}
                            </td>

                            <td class="px-3 py-2 whitespace-nowrap text-sm">
                                {{ format_number($item['line_total'],symbol: $currencyText) }}
                            </td>

                                <td class="px-3 py-2 whitespace-nowrap">


                                    <x-icon
                                        class="cursor-pointer text-red-500 hover:text-red-600 transition duration-150 ease-in-out"
                                        title="Sil" name="o-minus-circle"
                                        wire:click.prevent="removeItem({{ $index }})"/>


                                </td>

                            <td class="px-3 py-2 whitespace-nowrap">
                                <x-icon
                                    class="cursor-pointer text-blue-500 hover:text-blue-600 transition duration-150 ease-in-out"
                                    title="Düzenle" name="o-pencil-square"
                                    wire:click.prevent="editItem({{ $index }})"/>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold text-sm">
                    <tr>
                        <td colspan="3" class="px-3 py-2 text-right">Toplam Miktar:</td>
                        <td class="px-3 py-2">{{ $totalQuantity }}</td>
                    </tr>


                    @foreach($taxSummary as $taxRate => $taxAmount)
                       <tr>
                            <td colspan="3" class="px-3 py-2 text-right">Vergi Toplamı:</td>
                            <td class="px-3 py-2">{{ format_number($taxAmount, symbol: $currencyText) }}</td>
                        </tr>



                    @endforeach


                    <tr>
                        <td colspan="3" class="px-3 py-2 text-right">İskonto Tutarı: </td>
                        <td class="px-3 py-2">
                            {{ format_number($totalDiscountAmount, symbol: $currencyText) }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" class="px-3 py-2 text-right">Ara Toplam :</td>
                        <td class="px-3 py-2">
                            {{ format_number($subTotalAmount,symbol: $currencyText) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="px-3 py-2 text-right">Genel Toplam :</td>
                        <td class="px-3 py-2">
                            {{ format_number($totalAmount,symbol: $currencyText) }}
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <button wire:click.prevent="addItem"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-150 ease-in-out shadow-md hover:shadow-lg mt-4">
                Satır Ekle
            </button>
        </div>
    </div>
</div>


@script
<script>
    Alpine.data('xSelectedProducts', () => ({
        removeLine: function (key) {
            $wire = @this;
            $wire.selectedLine = key;

            modalConfirmDeleteLine.showModal();
        },

        updateLine: function (key) {
            $wire = @this;
            $wire.selectedLine = key;
            //console.log(key);
        },





        preventNegative: function (event) {
            if (event.keyCode === 189 || event.keyCode === 69) {
                event.preventDefault();
            }

            const value = event.target.value;

            if (value.startsWith('-') || value.startsWith('0')) {
               // event.target.value = 1;
            }
        },

        formatPrice: function (event) {
            this.preventNegative(event);

            const value = event.target.value;

            event.target.value = this.formatAsTL(value);
        },

        formatAsTL: function (value) {
            var cleanedValue = value.replace(/[^\d,]/g, '');

            var parts = cleanedValue.split(',');
            if (parts.length > 2) {shipment_items
                cleanedValue = parts[0] + ',' + parts[1].substring(0, 2);
            } else if (parts.length === 2 && parts[1].length > 2) {
                cleanedValue = parts[0] + ',' + parts[1].substring(0, 2);
            }

            const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            $wire.items[$wire.selectedLine]['unit_price'] = cleanedValue;

            return integerPart + (parts[1] ? ',' + parts[1] : '');
        },
    }));
</script>
@endscript
