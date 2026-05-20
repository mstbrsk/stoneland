<div class="bg-white rounded-lg shadow-md p-6">
    <x-modal wire:model="showVariantModal" title="Ürün Varyantları" class="bg-white rounded-lg shadow-xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($variants as $index => $variant)
                <div class="bg-gray-50 p-4 rounded-md">
                    <label
                        class="block text-sm font-medium text-gray-700 mb-2">{{ $variant->getVariantName(withProductName: true) }}</label>
                    <x-input type="number" wire:model.live.debounce="variantQuantities.{{ $index }}"
                             min="0"
                             :max="max($variant->stock, 0)"
                             :suffix="empty($variant->stock) ? ' 0 ' : $variant->stock"
                             class="w-full"
                             key="{{ Str::random() }}"/>
                </div>
            @endforeach
        </div>

        <x-slot:actions>
            <div class="flex justify-end space-x-3 mt-6">
                <x-button label="Kapat" @click="$wire.showVariantModal = false"
                          class="bg-gray-200 text-gray-700 hover:bg-gray-300"/>
                <x-button wire:click.prevent="saveVariantQuantities" label="Kaydet"
                          class="bg-blue-600 text-white hover:bg-blue-700"/>
            </div>
        </x-slot:actions>
    </x-modal>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Ürün
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">
                    Açıklama
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/10">
                    Miktar
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/10">
                    İşlemler
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" x-data="xSelectedProducts">
            @foreach ($items as $index => $item)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4">
                        <x-choices-offline
                            x-on:change="updateLine({{ $index }})"
                            wire:model.live.debounce="items.{{ $index }}.product_id"
                            :options="$products"
                            single
                            searchable
                            class="w-full"
                            style="height: 44px"
                        />
                    </td>

                    <td class="px-6 py-4">
                        <x-textarea
                            wire:model.lazy="items.{{ $index }}.notes"
                            rows="3"
                            class="w-full resize-none"
                            placeholder="Ürün açıklaması..."
                        />
                    </td>

                    <td class="px-6 py-4">
                        <div class="relative">
                            <input type="number"
                                   min="1"
                                   wire:model.lazy="items.{{ $index }}.qty"
                                   placeholder=""
                                   x-on:keyup="updateLine({{ $index }})"
                                   x-on:keydown="preventNegative"
                                   :disabled="true"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 pointer-events-none">

                                </span>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button wire:click.prevent="editItem({{ $index }})"
                                    class="text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                <x-icon name="o-pencil-square" class="w-5 h-5"/>
                            </button>
                            <button wire:click.prevent="removeItem({{ $index }})"
                                    class="text-red-600 hover:text-red-800 transition-colors duration-200">
                                <x-icon name="o-trash" class="w-5 h-5"/>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr class="bg-gray-50">
                <td colspan="2" class="px-6 py-4 text-right font-bold">Toplam Miktar:</td>
                <td colspan="2" class="px-6 py-4 font-bold">{{ $totalQuantity }} adet</td>
            </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-6 flex justify-between items-center">
        <button wire:click.prevent="addItem"
                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-full transition duration-200 flex items-center">
            <x-icon name="o-plus" class="w-5 h-5 mr-2"/>
            Yeni Ürün Ekle
        </button>
        <span class="text-sm text-gray-500">Toplam {{ count($items) }} ürün</span>
    </div>
</div>

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
