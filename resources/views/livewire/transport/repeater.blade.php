<div>
    <x-modal wire:model.live="showVariantModal" title="Ürün Varyantları">
        @php
            /** @var \App\Models\ProductVariant[] $variants */
        @endphp

        @foreach($variants as $index => $variant)
            <div>
                <label>{{ $variant->getVariantName(withProductName: true) }}</label>
                <x-input type="number" wire:model="variantQuantities.{{ $index }}"
                         :suffix="empty($variant->stock) ? ' 0 ' : $variant->stock"
                         key="{{ Str::random() }}"/>
            </div>
        @endforeach

        <x-slot:actions>
            <x-button label="Kapat" @click="$wire.showVariantModal = false"/>
            <x-button wire:click.prevent="saveVariantQuantities" label="Kaydet"/>
        </x-slot:actions>
    </x-modal>

    <table class="min-w-full divide-y divide-gray-200 table">
        <thead>
        <tr>

            <td class="px-16 py-4 whitespace-no-wrap bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">

                ÜRÜN DURUMU
            </td>


            <td class="px-16 py-4 whitespace-no-wrap bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">

                ÜRÜN
            </td>

            <td class="px-6 py-4 whitespace-no-wrap bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                AÇIKLAMA
            </td>

            <td class="px-6 py-4 whitespace-no-wrap bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                MİKTAR
            </td>



            <td class="px-6 py-4 whitespace-no-wrap bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                SİL
            </td>

            <td class="px-6 py-4 whitespace-no-wrap bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                TOPLAM
            </td>
            <th class="px-6 py-3 bg-gray-50"></th>
            <th class="px-6 py-3 bg-gray-50"></th>
            <th class="px-6 py-3 bg-gray-50"></th>
        </tr>
        </thead>
        <tbody x-data="xSelectedProducts">


        @foreach ($items as $index => $item)
            <tr>

                <td class="px-16 py-4 whitespace-no-wrap">

                    <x-choices-offline

                        wire:model="contact" single searchable
                        style="width:250px;height: 40px"
                        class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"/>
                </td>

                <td class="px-16 py-4 whitespace-no-wrap">

                    <x-choices-offline
                        x-on:change="updateLine({{ $index }})"
                        wire:model.live.debounce="items.{{ $index }}.product_id" :options="$products" single searchable
                        style="width:250px;height: 40px"
                        class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"/>
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <x-textarea wire:model.lazy="items.{{ $index }}.notes" rows="3"
                                class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3
                    leading-tight focus:outline-none focus:shadow-outline"/>
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <input type="number" min="1" wire:model.lazy="items.{{ $index }}.qty" placeholder="Miktar"
                           x-on:keyup="updateLine({{ $index }})"
                           x-on:keydown="preventNegative"
                           :disabled="true"
                           class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </td>



                <td class="px-6 py-4 whitespace-no-wrap">
                    <x-icon class="cursor-pointer" title="Sil" name="o-minus-circle" wire:click.prevent="removeItem({{ $index }})"/>
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <x-icon class="cursor-pointer" title="Düzenle" name="o-pencil-square" wire:click.prevent="editItem({{ $index }})"/>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3" class="text-right font-bold">Toplam Miktar:</td>
            <td>{{ $totalQuantity }}</td>
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
