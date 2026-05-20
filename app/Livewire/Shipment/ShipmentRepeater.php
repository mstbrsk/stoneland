<?php

namespace App\Livewire\Shipment;

use App\Models\Address;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleVariant;
use Illuminate\Support\Collection;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Mary\Exceptions\ToastException;
use Mary\Traits\Toast;

class ShipmentRepeater extends Component
{
    use Toast;

    public array $shipments = [];

    public Sale $sale;

    public ?Collection $saleVariants;
    public array $saleVariantList = [];
    public array $deliveryAddresses = [];

    public bool $hasMissing = false;

    #[Rule('sometimes|boolean')]
    public ?bool $can_printable = true;



    public function mount(): void
    {
        $this->saleVariantList = $this->saleVariants->map(fn(SaleVariant $saleVariant) => [
            'id' => $saleVariant->id,
            'name' => $saleVariant->variant->getVariantName(),

        ])
            ->toArray();


        $this->deliveryAddresses = $this->sale->contact->deliveryAddresses->map(fn(Address $address) => [
            'id' => $address->id,
            'name' => $address->name,
        ])
            ->toArray();

        $list = [];

        if ($this->shipments) {
            foreach ($this->shipments as $shipment) {
                $list[] = [
                    ...$shipment,
                    'remain_qty' => $shipment['sold_qty'] - collect($this->shipments)->where('variant_id', $shipment['variant_id'])->sum('qty'),
                    'disabled' => true,
                    'can_printable' => $shipment['can_printable'] ?? false,
                ];

            }
        }

        $this->shipments = $list;

        $this->hasMissing = collect($this->shipments)->sum('qty') < $this->sale->quantity;

        foreach ($this->saleVariantList as $saleVariantLis) {
            $this->addShipment(autoAdding: true);
        }

    }

    public function updatedShipments($value, $name): void
    {
        if (is_null($value)) {
            return;
        }



        if (str_contains($name, 'sale_variant_id')) {
            $index = (int)explode('.', $name)[0];

            $saleVariantId = $value;

            $variantId = collect($this->sale->variants)->where('id', $saleVariantId)->value('variant_id');

            if (is_null($variantId)) {
                return;
            }

            $soldQty = collect($this->sale->variants)->where('id', $saleVariantId)->value('qty');

            $this->shipments[$index]['qty'] = '';

            //$this->shipments[$index]['can_printable'];
            //$this->shipments[$index]['can_printable'] = $this->shipments[$index]['can_printable'] ?? false;

            if (str_contains($name, 'can_printable')) {
                $index = (int)explode('.', $name)[0];
                $this->shipments[$index]['can_printable'] = (bool)$value;
            }



            $this->shipments[$index]['sold_qty'] = $soldQty;
            $this->shipments[$index]['variant_id'] = $variantId;
            $this->shipments[$index]['product_id'] = ProductVariant::findOrFail($variantId)->product_id;


            $totalQty = collect($this->shipments)->where('variant_id', $variantId)->sum(fn(array $values) => (int)$values['qty']);

            $remainQty = $soldQty - $totalQty;

            $this->shipments[$index]['remain_qty'] = $remainQty;
        }

        if (str_contains($name, 'qty')) {
            $index = (int)explode('.', $name)[0];

            $soldQty = collect($this->sale->variants)->where('id', $this->shipments[$index]['sale_variant_id'])->value('qty');

            $totalQty = collect($this->shipments)->where('variant_id', $this->shipments[$index]['variant_id'])->sum(fn(array $values) => (int)$values['qty']);

            $remainQty = $this->shipments[$index]['remain_qty'];

            if ($totalQty > $soldQty) {

                $this->shipments[$index]['qty'] = $remainQty;

                $this->error('Kalan miktar kadar gönderim yapabilirsiniz!');

                return;
            }
        }

        $this->hasMissing = collect($this->shipments)->sum(fn(array $values) => (int)$values['qty']) < $this->sale->quantity;

        $this->dispatch('shipments-updated', $this->shipments);
    }






    /**
     * @throws ToastException
     */
    public function addShipment(bool $autoAdding = false): void
    {
        if (!$autoAdding) {
            foreach ($this->shipments as $item) {
                if (empty($item['qty']) || empty($item['shipment_address_id'])) {
                    throw ToastException::error('Lütfen tüm alanları doldurunuz!');
                }
            }
        }

        $this->shipments[] = [
            'product_id' => '',
            'sale_variant_id' => '',
            'variant_id' => '',
            'qty' => '',
            'sold_qty' => '',
            'remain_qty' => '',
            'shipment_address_id' => '',
            'disabled' => false,
            // 'can_printable' => '',
        ];
    }



    public function removeShipment($index): void
    {
        unset($this->shipments[$index]);

        $this->shipments = array_values($this->shipments);

        $this->updatedShipments('', '');
    }


    public function addProduct($index): void
    {
        unset($this->shipments[$index]);

        $this->shipments = array_values($this->shipments);

        $this->updatedShipments('', '');
    }


    public function render()
    {
        return view('livewire.shipments.repeater');
    }
}
