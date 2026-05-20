<div>
    <x-modal id="modalConfirmDeleteLine" title="Emin misin?">
        <div>Bu satır silinecek?</div>

        <x-slot:actions>
            <x-button label="Vazgeç" onclick="modalConfirmDeleteLine.close()"/>
            <x-button label="Onayla" class="btn-primary"
                      @click="$wire.removeInput($wire.selectedLine);modalConfirmDeleteLine.close()"/>
        </x-slot:actions>
    </x-modal>

    <table class="min-w-full divide-y divide-gray-200 table">
        <thead>
        <tr>
            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                Ürün
            </th>

            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                Açıklama
            </th>

            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                Miktar
            </th>

            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                Birim Fiyat
            </th>

            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                Vergi Oranı (%)
            </th>

            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                İskonto Oranı (%)
            </th>

            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                Vergi Tutarı
            </th>

            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                Toplam
            </th>
            <th class="px-6 py-3 bg-gray-50"></th>
        </tr>
        </thead>
        <tbody x-data="xSelectedProducts">
        @foreach ($items as $index => $item)

            <tr>
                <td class="px-6 py-4 whitespace-no-wrap">
                    <x-choices
                        x-on:change="updateLine({{ $index }})"
                        wire:model.live.debounce="items.{{ $index }}.product_id" :options="$products"
                        single
                        searchable
                        style="width: 250px;height: 40px"
                        search-function="search"
                        class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"/>
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <x-textarea wire:model.lazy="items.{{ $index }}.notes" rows="3"
                                class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3
                    leading-tight focus:outline-none focus:shadow-outline"/>
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <input type="number" min="1" wire:model.lazy="items.{{ $index }}.qty" placeholder="Miktar"
                           x-on:change="updateLine({{ $index }})"
                           x-on:keydown="preventNegative"
                           class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <input type="text" min="1" wire:model.lazy="items.{{ $index }}.formatted_price"
                           placeholder="Birim Fiyat"
                           x-on:change="updateLine({{ $index }})"
                           x-on:keyup="formatPrice"
                           class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <input type="number" min="1" wire:model.lazy="items.{{ $index }}.vat_rate"
                           placeholder="Vergi Oranı (%)"
                           x-on:change="updateLine({{ $index }})"
                           x-on:keydown="preventNegative"
                           class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <input type="number" min="0"  wire:model.lazy="items.{{ $index }}.discount_rate"
                           placeholder="İskonto Oranı (%)"
                           x-on:change="updateLine({{ $index }})"
                           x-on:keydown="preventNegative"
                           class="shadow appearance-none border border-blue-500 rounded w-20 py-2 px-3 text-gray-700   mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    {{ format_number($item['vat_line_total'],symbol: $currencyText) }}
                </td>

                <td class="px-6 py-4 w-44  whitespace-no-wrap">
                   {{ format_number($item['line_total'],symbol: $currencyText) }}

                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <button wire:click.prevent="removeItem({{ $index }})" class="text-red-600 hover:text-red-900">Sil
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3" class="text-right font-bold">Toplam Miktar:</td>
            <td>{{ $totalQuantity }}</td>
        </tr>




        <tr>
            <td colspan="3" class="text-right font-bold">İskonto Tutarı: </td>
            <td>{{ format_number($totalDiscountAmount, symbol: $currencyText) }} </td>
        </tr>


        <tr>
                <td colspan="3" class="text-right font-bold">Vergi Tutarı: </td>
                <td>{{ format_number($totalVatAmount, symbol: $currencyText) }} </td>
            </tr>



        <tr>
            <td colspan="3" class="text-right font-bold">Ara Toplam :</td>
            <td>
                {{ format_number($subTotalAmount,symbol: $currencyText) }}
            </td>
        </tr>
        <tr>
            <td colspan="3" class="text-right font-bold">Genel Toplam :</td>
            <td>
                {{ format_number($totalAmount,symbol: $currencyText) }}

            </td>
        </tr>
        </tfoot>
    </table>

    <button wire:click.prevent="addItem"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">Satır Ekle
    </button>
</div>

{{--
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', (el, component) => {
            console.log(el);
        });
    });
</script>
--}}

@script
<script>
    Alpine.data('xSelectedProducts', () => ({
        removeLine: function (key) {
            $wire =@this;
            $wire.selectedLine = key;

            modalConfirmDeleteLine.showModal();
        },

        updateLine: function (key) {
            $wire =@this;

            $wire.selectedLine = key;
        },

        preventNegative: function (event) {
            if (event.keyCode === 189 || event.keyCode === 69) {
                event.preventDefault();
            }

            const value = event.target.value;

            if (value.startsWith('-') || value.startsWith('0')) {
                event.target.value = 1;
            }
        },

        formatPrice: function (event) {
            this.preventNegative(event);

            const value = event.target.value;

            event.target.value = this.formatAsTL(value);
        },

        formatAsTL: function (value) {
            // Rakam ve virgül dışındaki karakterleri temizle
            var cleanedValue = value.replace(/[^\d,]/g, '');

            // Virgülden sonra iki basamak olacak şekilde formatla
            var parts = cleanedValue.split(',');
            if (parts.length > 2) {
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
