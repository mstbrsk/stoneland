<div>

    <style>


        .modal-box {
            min-width: 300px;
            height: 250px;
        }


       .asd > .modal-box  {
           max-width: 1500px;
           height: 350px;
       }


.modal-action {
    position: absolute;

}
/*.flex >.flex-1 > .input{ width: 50px;}

        .bg-primary\/5 {
            background-color: var(--fallback-p,oklch(var(--p)/0));
        }*/

    </style>

    <x-modal wire:model.live="showVariantModal" title="Ürün Varyantları"  class="asd" >
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

            @php
                /** @var \App\Models\ProductVariant[] $variants */
            @endphp
            @if(count($this->colors) > 1 && count($this->sizes) > 1 && !empty($this->variantMatrix))
            <table class="table-auto  ikili-varyant w-full bg-white rounded-lg shadow-md">
                <thead>
                <tr>
                    <th class="px-4 py-2 text-left bg-gray-200">Renkler \ Bedenler</th>
                    @foreach($sizes as $size)
                        <th class="px-4 py-2 bg-gray-200">{{ $size }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($colors as $color)
                    <tr>
                        <td class="px-4 py-2 font-bold">{{ $color }}</td>
                        @foreach($sizes as $size)
                            <td class="px-4 py-2">
                                @php
                                    $variant = $variantMatrix[$color][$size] ?? null;
                                @endphp

                                @if($variant)
                                    <x-input type="number" wire:model="variantQuantities.{{ $variant->id }}"
                                             :suffix="empty($variant->stock) ? ' 0 ' : $variant->stock"
                                             style="min-width: 70px;"  key="{{ Str::random() }}" min="0"/>
                                @else
                                 <!-- mevcut değil kısmı -->
                                @endif


                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>

            <table class="table-auto w-full bg-white rounded-lg shadow-md mt-4">
                <thead>
                <tr>
                    <th class="px-4 py-2 text-left bg-gray-200">Renk</th>
                    <th class="px-4 py-2 bg-gray-200">Toplam Miktar</th>
                </tr>
                </thead>
                <tbody>
                {{--@foreach($this->sumVariantMatrix() as $color => $totalQuantity)
                    <tr>
                        <td class="px-4 py-2 font-bold">{{ $color }}</td>
                        <td class="px-4 py-2">{{ $totalQuantity }}</td>
                    </tr>
                @endforeach --}}

                @foreach($this->sumVariantMatrix()['byColor'] as $color => $total)
                    <tr>
                        <td class="px-4 py-2 font-bold">{{ $color }}</td>
                        <td class="px-4 py-2">{{ $total }}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>

            @else
<!--  Tek varyant başlangıç  -->
            <table class="table-auto tekli-varyant w-full bg-white rounded-lg shadow-md"  >
                <thead>
                <tr>
                    <th class="px-4 py-2 text-left bg-gray-200">Renkler \ Bedenler xxx</th>
                    @foreach($variants as $index => $variant)
                        <th class="px-4 py-2 bg-gray-200">    {{ $variant->getVariantName(withProductName: true) }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>

                    <tr class="">
                    <td></td>
                        @foreach($variants as $index => $variant)

                        <td class="px-4 py-2 product">  <x-input type="number" wire:model="variantQuantities.{{ $index }}"
                                 :suffix="empty($variant->stock) ? ' 0 ' : $variant->stock"
                                   style="min-width: 70px;"   key="{{ Str::random() }}" min="0"/></td>
                        @endforeach

                    </tr>


                </tbody>
            </table>

            @endif
  <!--  Tek varyant bitiş  -->

            @foreach($variants as $index => $variant)
                <div style="display: none;">
                    <label>{{ $variant->getVariantName(withProductName: true) }}</label>
                    <x-input type="number" wire:model="variantQuantities.{{ $index }}"
                             :suffix="empty($variant->stock) ? ' 0 ' : $variant->stock"
                             key="{{ Str::random() }}"/>
                </div>
            @endforeach

            <x-slot:actions>

                <x-button label="Kapat" @click="$wire.showVariantModal = false;"/>
                <x-button wire:click.prevent="saveVariantQuantities" label="Kaydet"/>
            </x-slot:actions>
        </div>
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
                Vergi Tutarı
            </th>

            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                Toplam
            </th>
            <th class="px-6 py-3 bg-gray-50"></th>
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
                        search-function="search"
                        style="width: 250px;height: 40px"
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




                <!--  <input type="text" min="1" wire:model.lazy="items.{{ $index }}.formatted_price"
                           placeholder="Birim Fiyat"
                           x-on:keydown="updateLine({{ $index }})"
                           x-on:keyup="formatPrice"
                           x-on:change="formatPrice" />-->

                  <!--  <input type="text"
                           x-model="items[{{ $index }}].formatted_price"
                           placeholder="Birim Fiyat"
                           @blur="formatPrice($event)"
                           class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">-->




                    @if (!$isEdit)
                        <input type="text"
                               x-model="items[{{ $index }}].formatted_price"

                               placeholder="Birim Fiyat"
                               @blur="formatPrice($event, {{ $index }})"
                               class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                        @else
                        <input type="text" min="1" wire:model.lazy="items.{{ $index }}.formatted_price"
                               placeholder="Birim Fiyat"

                               @blur="updateLine({{ $index }})"
                               class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                               />
                    @endif







                </td>





                <td class="px-6 py-4 whitespace-no-wrap">
                    <input type="number" min="1" wire:model.lazy="items.{{ $index }}.vat_rate"
                           placeholder="Vergi Oranı (%)"
                           x-on:keyup="updateLine({{ $index }})"
                           x-on:keydown="preventNegative"
                           class="shadow appearance-none border border-blue-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    {{ format_number($item['vat_line_total'],symbol: $currencyText) }}
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    {{ format_number($item['line_total'],symbol: $currencyText) }}
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <x-icon class="cursor-pointer" title="Sil" name="o-minus-circle"
                            wire:click.prevent="removeItem({{ $index }})"/>
                </td>

                <td class="px-6 py-4 whitespace-no-wrap">
                    <x-icon class="cursor-pointer" title="Düzenle" name="o-pencil-square"
                            wire:click.prevent="editItem({{ $index }})"/>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3" class="text-right font-bold">Toplam Miktar:</td>
            <td>{{ $totalQuantity }}</td>
        </tr>


        @foreach($taxSummary as $taxRate => $taxAmount)
            <tr>
                <td colspan="3" class="text-right font-bold">Vergi Oranı: %{{ $taxRate }}</td>
                <td>{{ format_number($taxAmount, symbol: $currencyText) }}</td>
            </tr>
        @endforeach

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
        items: @entangle('items').defer,
        subItems: @entangle('subItems').defer,
        init() {
            this.$watch('items', value => {
                // items dizisi değiştiğinde yapılacak işlemler
                console.log('items updated:', value);
            });
        },

        removeLine: function (key) {
            $wire =@this;
            $wire.selectedLine = key;
            modalConfirmDeleteLine.showModal();
        },

        updateLine: function (key) {
            $wire =@this;
            $wire.selectedLine = key;
            const priceInput = document.querySelector(`input[wire\\:model\\.lazy="items.${key}.formatted_price"]`);
            if (priceInput) {
                // formatPrice fonksiyonunu çağır
                this.formatPrice({ target: priceInput });
            }

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
            const formattedValue = this.formatAsTL(value);

            event.target.value = formattedValue;

            // Livewire modelini güncelle
            $wire.set(`items.${$wire.selectedLine}.formatted_price`, formattedValue);
        },





         /*formatAsTL: function (value) {
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

             // Livewire modelini güncelle
             $wire.set(`items.${$wire.selectedLine}.unit_price`, cleanedValue.replace(',', '.'));

             return integerPart + (parts[1] ? ',' + parts[1] : '');
         },*/

        formatAsTL: function (value) {
            // Eğer değer boşsa veya sadece virgül içeriyorsa, işlem yapma
            if (!value || value === ',') {
                return value;
            }

            // Rakam ve virgül dışındaki karakterleri temizle
            let cleanedValue = value.replace(/[^\d,]/g, '');

            // Birden fazla virgül varsa, sadece ilkini koru
            let parts = cleanedValue.split(',');
            cleanedValue = parts[0] + (parts.length > 1 ? ',' + parts[1] : '');

            // Eğer değer virgülle bitiyorsa, formatlamayı erken sonlandır
            if (cleanedValue.endsWith(',')) {
                return cleanedValue;
            }

            // Virgülü noktaya çevir
            cleanedValue = cleanedValue.replace(',', '.');

            // Sayıyı float'a çevir ve iki ondalık basamağa yuvarla
            let number = parseFloat(cleanedValue);
            if (isNaN(number)) {
                return value; // Geçersiz sayı ise orijinal değeri döndür
            }

            // Sayıyı formatla
            let formattedValue = new Intl.NumberFormat('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number);

            // Livewire modelini güncelle (backend için nokta kullan)
            $wire.set(`items.${$wire.selectedLine}.unit_price`, number.toFixed(2));

            return formattedValue;
        },

    }));









</script>


@endscript
