<div @if ($isEdit) style="pointer-events: none;opacity: 0.4;" @endif>
    <table class="table">
        <thead>
        <tr class="item-row">
            <th></th>
            <th>Nitelik</th>
            <th>Değerler</th>
        </tr>

        <tbody x-data="xStockAttributes">
        @foreach($selectedProductAttributes as $key => $attribute)

            <tr>

                {{--Action--}}
                <td>
                    <x-mary-icon name="o-trash"
                                 @click="removeLine({{ $key }})" {{--wire:click.prevent="removeInput({{ $key }})"--}}/>
                </td>

                {{--Nitelikler--}}
                <td class="w-2/5">
                    <x-choices-offline
                        searchable
                        placeholder="Nitelik şeçiniz"
                        x-on:change="updateLine({{ $key }})"
                        single
                        :options="$productAttributes"
                        wire:model.live="selectedProductAttributes.{{$key}}.attribute_id"
                        id="selectedProductAttributes_{{$key}}_attribute_id" style="height: 45px"/>

                    @error('selectedProductAttributes.'.$key.'.attribute_id') <span
                        class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </td>

                @if (!empty($attribute['attribute_id']))
                    {{--Değerler--}}
                    <td class="w-3/5">
                        <x-choices-offline placeholder="Seçiniz" searchable=""
                                           multiple
                                           :options="$attributeItems[$attribute['attribute_id']]"

                                           wire:model.live="selectedProductAttributes.{{ $key }}.values"
                                           id="selectedProductAttributes_{{ $key }}_values"
                                           class="h-96"
                        />
                    </td>
                @endif
            </tr>
        @endforeach

        <tr id="hiderow">
            <td colspan="4">
                <a wire:click.prevent="addInput" class="btn btn-primary">Satır Ekle</a>
            </td>
        </tr>
    </table>

    {{-- <x-modal id="modalConfirmDeleteLine" title="Emin misin?">
         <div>Bu satır silinecek?</div>

         <x-slot:actions>
             <x-button label="Vazgeç" onclick="modalConfirmDeleteLine.close()"/>
             <x-button label="Onayla" class="btn-primary"
                       @click="$wire.removeInput($wire.selectedLine);modalConfirmDeleteLine.close()"/>
         </x-slot:actions>
     </x-modal>--}}
</div>

@script
<script>
    Alpine.data('xStockAttributes', () => ({
        removeLine: function (key) {
            const c = confirm('Bu satır silinecek, emin misiniz?');

            if (c) {
                $wire =@this;
                $wire.removeInput(key);
            }
        },

        updateLine: function (key) {
            $wire =@this;

            const qty = $wire.get(`selectedProducts.${key}.qty`);
            const price = $wire.get(`selectedProducts.${key}.unit_price`);
            const taxRate = $wire.get(`selectedProducts.${key}.tax_rate`);

            let lineTotal = price * qty;

            const vat = lineTotal * (taxRate / 100);

            lineTotal += vat;

            $wire.set(`selectedProducts.${key}.tax_price`, vat);
            $wire.set(`selectedProducts.${key}.line_total`, lineTotal);
        },
    }))
</script>
@endscript
