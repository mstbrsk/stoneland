<?php

namespace App\Livewire\ProductAttributes;

use Illuminate\Support\Collection;
use Livewire\Component;

class ProductAttributeRepeater extends Component
{
    public Collection $selectedProductAttributes;

    public bool $isEdit = false;

    public function __construct()
    {
        $this->selectedProductAttributes = collect();
    }

    public function addLine()
    {
        $this->selectedProductAttributes->push([
            'attribute' => '',
        ]);
    }

    public function updated()
    {
        $this->dispatch('raise-updated-selected-product-attributes', $this->selectedProductAttributes->toArray());
    }

    public function removeLine($key)
    {
        $this->selectedProductAttributes->pull($key);
    }

    public function mount()
    {
        if (!$this->isEdit) {
            $this->fill([
                'selectedProductAttributes' => collect([[
                    'attribute' => '',
                ]]),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.product-attributes.product-attribute-repeater');
    }
}
