<div>
    @csrf
    <table class="table">
        <thead>
        <tr class="item-row">
            <th>&nbsp;</th>
            <th>Değerler</th>
        </tr>

        <tbody x-data="xProductAttributes">
        @foreach($selectedProductAttributes as $key => $attribute)
            <tr>
                {{--Action--}}
                <td>
                    @if (!$isEdit)
                        <x-mary-icon name="o-trash" disabled wire:click.prevent="removeLine({{ $key }})"/>
                    @endif
                </td>

                {{--Değer--}}
                <td>

                    @if ($isEdit)
                        <x-input placeholder="Değer" disabled
                                 wire:model.live="selectedProductAttributes.{{$key}}.attribute"
                                 id="selectedProductAttributes{{$key}}_attribute"/>
                    @else
                        <x-input placeholder="Değer"
                                 wire:model.live="selectedProductAttributes.{{$key}}.attribute"
                                 id="selectedProductAttributes{{$key}}_attribute"/>
                    @endif

                </td>
            </tr>
        @endforeach

        @if (!$isEdit)
            <tr>
                <td colspan="4">
                    <a wire:click.prevent="addLine" class="btn btn-primary">Satır Ekle</a>
                </td>
            </tr>
        @endif
    </table>
</div>

@script
<script>
    Alpine.data('xProductAttributes', () => ({
        removeLine: function (key) {
            $wire =@this;
            $wire.selectedLine = key;
        },
    }));
</script>
@endscript
