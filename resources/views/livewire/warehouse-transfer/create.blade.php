<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    #[Rule('required')]
    public ?int $from = null;

    #[Rule('required')]
    public int $to;

    #[Rule('required')]
    public string $productId;

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;

    public ?\App\Models\Warehouse $toWarehouse;
    public ?\App\Models\Warehouse $fromWarehouse;

    public bool $showVariantModal = false;
    public ?\Illuminate\Support\Collection $variants;
    public ?\Illuminate\Support\Collection $products;

    public ?\Illuminate\Support\Collection $selectedVariants;

    public ?\Illuminate\Support\Collection $selectedTransfers;

    public function mount()
    {
        $this->products = collect();

        $this->selectedVariants = collect();
        $this->selectedTransfers = collect();
    }

    public function updatedProductId(mixed $value): void
    {
        $this->selectedVariants = collect();

        $this->variants = \App\Models\ProductVariant::whereProductId($value)->get();
        $this->showVariantModal = true;
    }

    public function updatedFrom(mixed $value): void
    {
        $this->products = \App\Models\Product::where('warehouse_id', $value)
            ->get()->map(fn(\App\Models\Product $product) => [
                'name' => $product->name,
                'id' => $product->id,
            ]);

        $this->fromWarehouse = \App\Models\Warehouse::findOrFail($value);
    }

    public function updatedTo(mixed $value): void
    {
        $this->toWarehouse = \App\Models\Warehouse::findOrFail($value);
    }

    public function addVariants()
    {
        if ($this->from === $this->to) {
            throw \Mary\Exceptions\ToastException::error('Aynı depolara transfer yapılamaz!');
        }

        $selectedVariants = $this->selectedVariants->toArray();

        $selectedTransfers = \App\Models\ProductVariant::whereIn('id', array_keys($selectedVariants))
            ->get()
            ->map(fn(\App\Models\ProductVariant $variant) => [
                'name' => $variant->getVariantName(),
                'qty' => (int)$selectedVariants[$variant->id],
                'real_stock' => $variant->stock,
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
            ]);

        $selectedTransfers->map(function (array $variant) {
            if ($variant['qty'] > $variant['real_stock']) {
                throw \Mary\Exceptions\ToastException::error("{$variant['name']} ürünü için transfer miktarı en fazla {$variant['real_stock']} olabilir!");
            }
        });

        $this->selectedTransfers = $selectedTransfers;

        $this->showVariantModal = false;
    }

    public function save(): void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $transferId = Str::uuid()->toString();

        $this->selectedTransfers->map(function (array $variant) use ($transferId) {
            \App\Models\WarehouseTransfer::create([
                'transfer_id' => $transferId,
                'from' => $this->from,
                'to' => $this->to,
                'product_id' => $variant['product_id'],
                'variant_id' => $variant['variant_id'],
                'qty' => $variant['qty'],
                'created_by' => auth('web')->id(),
                'updated_by' => auth('web')->id(),
            ]);

            \App\Models\ProductTransaction::create([
                'product_id' => $variant['product_id'],
                'variant_id' => $variant['variant_id'],
                'quantity' => $variant['qty'],
                'type' => \App\Enums\StockProcessType::TRANSFER,
                'relation_type' => \App\Enums\ProductStock\RelationType::WAREHOUSE_TRANSFER,
                'relation_id' => $transferId,
                'created_by' => auth('web')->id(),
                'warehouse_id' => $this->to,
                'notes' => "{$this->fromWarehouse->name} -> {$this->toWarehouse->name}"
            ]);

            $toInventory = \App\Models\Inventory::create([
                'warehouse_id' => $this->to,
                'product_id' => $variant['product_id'],
                'variant_id' => $variant['variant_id'],
                'created_by' => auth('web')->id(),
                'updated_by' => auth('web')->id(),
            ]);

            $toInventory->increment('quantity', $variant['qty']);

            $fromInventory = \App\Models\Inventory::create([
                'warehouse_id' => $this->from,
                'product_id' => $variant['product_id'],
                'variant_id' => $variant['variant_id'],
                'created_by' => auth('web')->id(),
                'updated_by' => auth('web')->id(),
            ]);

            $fromInventory->decrement('quantity', $variant['qty']);
        });

        log_action(message: 'Depo transferi oluşturuldu', relationType: 'WarehouseTransfer', relationId: $transferId);

        $this->success('Depo transferş oluşturuldu.', redirectTo: '/warehouse-transfers');

        $this->dispatch('warehouse-transfer-created');
    }

    public function with(): array
    {
        return [
            'warehouses' => \App\Models\Warehouse::all(),
        ];
    }
};
?>

<div>
    <style>
        table.GeneratedTable {
            width: 100%;
            background-color: #ffffff;
            border-collapse: collapse;
            border-width: 2px;
            border-color: #ffcc00;
            border-style: solid;
            color: #000000;
        }

        table.GeneratedTable td, table.GeneratedTable th {
            border-width: 2px;
            border-color: #ffcc00;
            border-style: solid;
            padding: 3px;
        }

        table.GeneratedTable thead {
            background-color: #ffcc00;
        }
    </style>

    <x-modal wire:model="showVariantModal" title="Ürün Varyantları">
        @php
            /** @var \App\Models\ProductVariant[] $variants */
        @endphp
        @foreach($variants ?? [] as $key => $variant)
            <div style="padding: 10px">
                <label>
                    {{ $variant->getVariantName(withProductName: true) }}
                </label>

                <x-input :suffix="'Toplam:'. get_variant_qty($variant->id)" type="number"
                         wire:model="selectedVariants.{{ $variant->id }}"
                         key="{{ Str::random() }}"/>
            </div>
        @endforeach

        <x-slot:actions>
            <x-button label="Kapat" @click="$wire.showVariantModal = false"/>
            <x-button label="Ekle" class="btn-primary" wire:click.prevent="addVariants"/>
        </x-slot:actions>
    </x-modal>

    <x-header title="Depo Transfer" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Depo Transfer" subtitle="Depo transfer giriniz" size="text-2xl"/>
            </div>

            <div class="col-span-3 grid gap-3">

                <x-choices-offline single searchable label="Depo Adı" wire:model.live.debounce.1000ms="from" required
                                   wire:model.live.debounce.1000ms="from"
                                   :options="$warehouses" style="height: 45px"/>

                <x-choices-offline single searchable label="Transfer Depo" wire:model="to" required
                                   wire:model="to"
                                   :options="$warehouses" style="height: 45px"/>

                <x-choices-offline single searchable wire:model.live.debounce.1000ms="productId" :options="$products"
                                   label="Ürün" required
                                   style="height: 45px"/>

                @if ($selectedTransfers->isNotEmpty())
                    <table class="GeneratedTable">
                        <thead>
                        <tr>
                            <th>Varyant Adı</th>
                            <th>Miktar</th>
                        </tr>
                        </thead>

                        @foreach($selectedTransfers->toArray() as $selectedTransfer)
                            <tr>
                                <td>{{ $selectedTransfer['name'] }}</td>
                                <td>{{ $selectedTransfer['qty'] }}</td>
                            </tr>
                        @endforeach

                        <tbody></tbody>
                    </table>
                @endif
            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/warehouses"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
