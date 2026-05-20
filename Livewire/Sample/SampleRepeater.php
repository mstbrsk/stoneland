<?php

namespace App\Livewire\Sample;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Exceptions\ToastException;
use Mary\Traits\Toast;

class SampleRepeater extends Component
{
    use Toast;

    public bool $isEdit = false;
    public bool $createFirstLineOnInit = false;

    public Collection|array|null $items = [];
    public ?Collection $products;

    public int $selectedLine = 0;

    public int $totalQuantity = 0;

    public bool $showVariantModal = false;
    public $variants = [];
    public $variantQuantities = [];

    public function mount(): void
    {
       /* if ($this-6>createFirstLineOnInit && !$this->isEdit) {
            $this->items[] = $this->resetValues();
        }*/

        $this->products = Product::get()->map(fn(Product $product) => [
            'name' => $product->stock_code,
            'id' => $product->id,
        ]);
    }


    public function resetValues(): array
    {
        return [
            'product_id' => '',
            'notes' => '',
            'qty' => null,
        ];
    }

    public function updatedVariantQuantities($qty, $index)
    {
        /** @var ProductVariant $productVariant */
        $productVariant = $this->variants[$index];

        if ($productVariant->stock < 0) {
            $this->variantQuantities[$index] = 0;
            return;
        }

        if ($qty > $productVariant->stock) {
            $this->variantQuantities[$index] = $productVariant->stock;
        }
    }

    public function updatedItems($value, $name): void
    {
        if (str_contains($name, 'product_id') && $value) {
            $exists = collect($this->items)->where('product_id', $value)->whereNotNull('qty')->first();


            $index = explode('.', $name)[0];

            $this->selectedLine = $index;
           
            if ($exists) {
                $this->error('Bu ürün zaten listede mevcut!');

                $this->items[$this->selectedLine] = $this->resetValues();

                return;
            }

            $this->variants = ProductVariant::where('product_id', $value)->get();
            $this->variantQuantities = array_fill(0, count($this->variants), '');
            $this->showVariantModal = true;
        }
    }

    public function saveVariantQuantities(): void
    {
        foreach ($this->variants as $index => $variant) {
            if (empty($this->variantQuantities[$index])) {
                continue;
            }

            $this->items[$this->selectedLine]['variants'][$variant->id] = (int)$this->variantQuantities[$index];
        }

        $this->items[$this->selectedLine]['qty'] = collect($this->variantQuantities)->sum(fn($value) => !empty($value) ? $value : 0);

        $this->showVariantModal = false;

        $this->totalQuantity = collect($this->items)->sum('qty');

        $this->dispatch('raise-selected-products', $this->items);
    }

    /**
     * @throws ToastException
     */
    public function addItem(): void
    {
        foreach ($this->items as $item) {
            if (empty($item['product_id'])) {
                throw ToastException::error('Ürün seçmeden alt satıra geçmeyin!');
            }
        }

        $this->items[] = $this->resetValues();
    }

    public function removeItem($index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function editItem($index)
    {
        $product_id = $this->items[$index]['product_id'];

        if ($product_id) {
            $this->selectedLine = $index;

            $this->variants = ProductVariant::where('product_id', $product_id)->get();

            if (isset($this->items[$index]['variants'])) {
                $this->variantQuantities = array_values($this->items[$index]['variants']) ?: array_fill(0, $this->variants->count(), 0);

                $this->showVariantModal = true;
            }
        }
    }

    public function render()
    {
        return view('livewire.samples.repeater');
    }
}
