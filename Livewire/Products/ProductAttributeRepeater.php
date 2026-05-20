<?php

namespace App\Livewire\Products;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductAttributeRepeater extends Component
{
    public bool $isEdit = false;

    public ?Collection $attributeItems = null;

    public Collection $selectedProductAttributes;

    public Collection $productAttributes;


    public function mount()
    {
        if (!$this->isEdit) {
            $this->fill([
                'selectedProductAttributes' => collect([[
                    'attribute_id' => '',
                    'values' => [],
                ]]),

               'productAttributes' => ProductAttribute::all(),

                'attributeItems' => collect(),

            ]);
        }

        if ($this->isEdit) {
            $this->fill([
                'attributeItems' => collect(),
                'productAttributes' => ProductAttribute::all(),
            ]);

            foreach ($this->selectedProductAttributes as $attribute) {

                $items = ProductAttributeItem::where('product_attribute_id', $attribute['attribute_id'])->get()
                      ->map(fn(ProductAttributeItem $item) => [
                        'id' => $item->id,
                        'name' => $item->value,

                    ]);

                $this->attributeItems[$attribute['attribute_id']] = $items;

            }
        }
    }

    public function updated($name, $value)
    {

        if (Str::endsWith($name, 'attribute_id')) {

           $items = ProductAttributeItem::where('product_attribute_id', $value)->get()
                ->map(fn(ProductAttributeItem $item) => [
                    'id' => $item->id,
                    'name' => $item->value,
                ])
                ->toArray();

            $this->attributeItems[$value] = $items;

        }

        if (Str::endsWith($name, 'values')) {
            $this->dispatch('raise-updated-attribute-items', $this->selectedProductAttributes->toArray());
        }
    }

    public function addInput()
    {
        $this->selectedProductAttributes->push([
            'attribute_id' => '',
            'values' => [],
        ]);
    }


    public function removeInput($key)
    {
        $this->selectedProductAttributes->pull($key);
    }

    public function render()
    {
        return view('livewire.products.product-attribute-repeater');
    }
}
